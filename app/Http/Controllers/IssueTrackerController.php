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
    protected function handleReporterInformation(Project $project, array $validated, Request $request): void
    {
        // Auto-transfer reporter information to client's staff_information
        $client = $project->client;
        if ($client) {
            $reporterName = $validated['name'];
            $reporterEmail = in_array($validated['communication_preference'], ['email', 'both'])
                ? ($request->input('email') ?? null)
                : null;
            $reporterWhatsapp = in_array($validated['communication_preference'], ['whatsapp', 'both'])
                ? ($request->input('whatsapp_number') ?? null)
                : null;

            // Normalize phone number to digits only
            $normalizedWhatsapp = static::normalizePhoneNumber($reporterWhatsapp);

            // Smart duplicate detection and staff management
            $existingStaff = $client->staff_information ?? [];
            $isDuplicate = false;

            foreach ($existingStaff as $staff) {
                $existingName = $staff['staff_name'] ?? null;
                $existingEmail = $staff['staff_email'] ?? null;
                $existingContact = $staff['staff_contact_number'] ?? null;

                // Name matching (case-insensitive, trimmed)
                $nameMatch = $existingName &&
                             strtolower(trim($existingName)) === strtolower(trim($reporterName));

                // Email matching (case-insensitive, trimmed)
                $emailMatch = $existingEmail && $reporterEmail &&
                              strtolower(trim($existingEmail)) === strtolower(trim($reporterEmail));

                // Phone matching (normalized digits comparison)
                $phoneMatch = false;
                if ($normalizedWhatsapp && $existingContact) {
                    $normalizedExisting = static::normalizePhoneNumber($existingContact);
                    $phoneMatch = $normalizedExisting === $normalizedWhatsapp;
                }

                // Smart duplicate detection: Only consider exact matches as duplicates
                if (($nameMatch && $phoneMatch) || // Same name AND same phone
                    ($nameMatch && $emailMatch && $emailMatch !== null) || // Same name AND same email
                    ($phoneMatch && $emailMatch && $emailMatch !== null)) { // Same phone AND same email
                    $isDuplicate = true;
                    break;
                }
            }

            // Handle staff information storage/updates
            if (! $isDuplicate && (! empty($reporterName) || ! empty($normalizedWhatsapp))) {
                // Check if we have existing staff with same name (to update contact info)
                $existingStaffIndex = null;
                foreach ($existingStaff as $index => $staff) {
                    $existingName = $staff['staff_name'] ?? null;
                    if ($existingName && strtolower(trim($existingName)) === strtolower(trim($reporterName))) {
                        $existingStaffIndex = $index;
                        break;
                    }
                }

                if ($existingStaffIndex !== null) {
                    // Update existing staff with new contact information
                    if (! empty($normalizedWhatsapp)) {
                        $existingStaff[$existingStaffIndex]['staff_contact_number'] = $normalizedWhatsapp;
                        $existingStaff[$existingStaffIndex]['staff_contact_number_country'] = static::detectCountryCode($normalizedWhatsapp);
                    }
                    if (! empty($reporterEmail)) {
                        $existingStaff[$existingStaffIndex]['staff_email'] = trim($reporterEmail);
                    }
                    // Update timestamp to show this contact info was recently verified
                    $existingStaff[$existingStaffIndex]['updated_at'] = now()->toISOString();
                } else {
                    // Create new staff entry
                    $countryCode = static::detectCountryCode($normalizedWhatsapp ?? '');
                    $staffEmail = (! empty($reporterEmail) && ! empty($normalizedWhatsapp)) ? trim($reporterEmail) : null;

                    $newStaff = [
                        'staff_name' => trim($reporterName) ?: null,
                        'staff_email' => $staffEmail,
                        'staff_contact_number' => $normalizedWhatsapp,
                        'staff_contact_number_country' => $countryCode,
                        'added_from' => 'issue_tracker',
                        'added_at' => now()->toISOString(),
                        'communication_preference' => $validated['communication_preference'],
                    ];

                    // Remove null values
                    $newStaff = array_filter($newStaff, function ($value) {
                        return $value !== null && $value !== '';
                    });

                    if (! empty($newStaff)) {
                        $existingStaff[] = $newStaff;
                    }
                }

                if (! empty($existingStaff)) {
                    $client->staff_information = $existingStaff;
                    $client->save();
                }
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
