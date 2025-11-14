<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWishlistRequest;
use App\Models\Project;
use Illuminate\Http\Request;

class WishlistTrackerController extends BaseTrackerController
{
    public function __construct()
    {
        $this->trackerStatus = 'wishlist';
        $this->routePrefix = 'wishlist-tracker';
        $this->projectCodeField = 'wishlist_tracker_code';
        $this->tokenPrefix = 'CHEQQ-WSH-';
        $this->viewDirectory = 'wishlist-tracker';
    }

    /**
     * Get the form request class for validation.
     */
    protected function getFormRequestClass(): string
    {
        return StoreWishlistRequest::class;
    }

    /**
     * Build extra information array for the task.
     */
    protected function buildExtraInformation(array $validated, Request $request): array
    {
        $communicationPreference = $validated['communication_preference'];

        return [
            [
                'title' => 'Requester Name',
                'value' => $validated['name'],
            ],
            [
                'title' => 'Communication Preference',
                'value' => $communicationPreference === 'email' ? 'Email' : ($communicationPreference === 'whatsapp' ? 'WhatsApp' : 'Both (Email & WhatsApp)'),
            ],
            ...($communicationPreference === 'both' ? [
                [
                    'title' => 'Requester Email',
                    'value' => $request->input('email', ''),
                ],
                [
                    'title' => 'Requester WhatsApp',
                    'value' => $request->input('whatsapp_number', ''),
                ],
            ] : [
                [
                    'title' => $communicationPreference === 'email' ? 'Requester Email' : 'Requester WhatsApp',
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
     * Handle requester information processing.
     */
    protected function handleRequesterInformation(Project $project, array $validated): void
    {
        // Auto-transfer requester information to client's staff_information
        $client = $project->client;
        if ($client) {
            $requesterName = $validated['name'];
            $requesterEmail = in_array($validated['communication_preference'], ['email', 'both'])
                ? ($validated['email'] ?? null)
                : null;
            $requesterWhatsapp = in_array($validated['communication_preference'], ['whatsapp', 'both'])
                ? ($validated['whatsapp_number'] ?? null)
                : null;

            // Normalize phone number to +country_code format
            $normalizedWhatsapp = static::normalizePhoneNumber($requesterWhatsapp);

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
                    'staff_name' => $requesterName,
                    'staff_email' => $requesterEmail,
                    'staff_contact_number' => $normalizedWhatsapp,
                ];

                $client->staff_information = $existingStaff;
                $client->save();
            }
        }
    }

    /**
     * Handle requester information processing.
     */
    protected function handleReporterInformation(Project $project, array $validated): void
    {
        // Auto-transfer requester information to client's staff_information
        $client = $project->client;
        if ($client) {
            $requesterName = $validated['name'];
            $requesterEmail = in_array($validated['communication_preference'], ['email', 'both'])
                ? ($validated['email'] ?? null)
                : null;
            $requesterWhatsapp = in_array($validated['communication_preference'], ['whatsapp', 'both'])
                ? ($validated['whatsapp_number'] ?? null)
                : null;

            // Normalize phone number to +country_code format
            $normalizedWhatsapp = static::normalizePhoneNumber($requesterWhatsapp);

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
                    'staff_name' => $requesterName,
                    'staff_email' => $requesterEmail,
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
        return 'Your wishlist item has been submitted successfully. Thank you!';
    }
}
