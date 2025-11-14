<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class IssueTrackerController extends BaseTrackerController
{
    public function __construct()
    {
        $this->trackerStatus = 'issue_tracker';
        $this->routePrefix = 'issue-tracker';
        $this->projectCodeField = 'issue_tracker_code';
        $this->tokenPrefix = 'CHEQQ-ISU-';
        $this->viewDirectory = 'issue-tracker';
    }

    /**
     * Get the form request class for validation.
     */
    protected function getFormRequestClass(): string
    {
        return \App\Http\Requests\StoreIssueTicketRequest::class;
    }

    /**
     * Build extra information array for the task.
     */
    protected function buildExtraInformation(array $validated, Request $request): array
    {
        $communicationPreference = $validated['communication_preference'];

        return [
            [
                'title' => 'Reporter Name',
                'value' => $validated['name'],
            ],
            [
                'title' => 'Communication Preference',
                'value' => $communicationPreference === 'email' ? 'Email' : ($communicationPreference === 'whatsapp' ? 'WhatsApp' : 'Both (Email & WhatsApp)'),
            ],
            ...($communicationPreference === 'both' ? [
                [
                    'title' => 'Reporter Email',
                    'value' => $request->input('email', ''),
                ],
                [
                    'title' => 'Reporter WhatsApp',
                    'value' => $request->input('whatsapp_number', ''),
                ],
            ] : [
                [
                    'title' => $communicationPreference === 'email' ? 'Reporter Email' : 'Reporter WhatsApp',
                    'value' => $communicationPreference === 'email'
                        ? $request->input('email', '')
                        : $request->input('whatsapp_number', ''),
                ],
            ]),
            [
                'title' => 'Submitted on',
                'value' => now()->format('j/n/y, h:i A'),
            ],
        ];
    }

    /**
     * Handle reporter information processing.
     */
    protected function handleReporterInformation(Project $project, array $validated): void
    {
        // Auto-transfer reporter information to client's staff_information
        $client = $project->client;
        if ($client) {
            $reporterName = $validated['name'];
            $reporterEmail = in_array($validated['communication_preference'], ['email', 'both'])
                ? ($validated['email'] ?? null)
                : null;
            $reporterWhatsapp = in_array($validated['communication_preference'], ['whatsapp', 'both'])
                ? ($validated['whatsapp_number'] ?? null)
                : null;

            // Normalize phone number to +country_code format
            $normalizedWhatsapp = static::normalizePhoneNumber($reporterWhatsapp);

            // Check for duplicates (based on normalized phone number only)
            $existingStaff = $client->staff_information ?? [];
            $isDuplicate = false;

            if ($normalizedWhatsapp) {
                foreach ($existingStaff as $staff) {
                    $existingContact = $staff['staff_contact_number'] ?? null;
                    if ($existingContact) {
                        $normalizedExisting = static::normalizePhoneNumber($existingContact);
                        if ($normalizedExisting === $normalizedWhatsapp) {
                            $isDuplicate = true;
                            break;
                        }
                    }
                }
            }

            // Add if not duplicate
            if (! $isDuplicate) {
                $existingStaff[] = [
                    'staff_name' => $reporterName,
                    'staff_email' => $reporterEmail,
                    'staff_contact_number' => $normalizedWhatsapp,
                ];

                $client->staff_information = $existingStaff;
                $client->save();
            }
        }
    }

    /**
     * Get success message after submission.
     */
    protected function getSuccessMessage(): string
    {
        return 'Your issue has been submitted successfully. Thank you!';
    }
}
