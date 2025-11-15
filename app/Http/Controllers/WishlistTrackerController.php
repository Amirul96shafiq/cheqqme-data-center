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
    protected function handleRequesterInformation(Project $project, array $validated, Request $request): void
    {
        // Auto-transfer requester information to client's staff_information
        $client = $project->client;
        if ($client) {
            $requesterName = $validated['name'];
            $requesterEmail = in_array($validated['communication_preference'], ['email', 'both'])
                ? ($request->input('email') ?? null)
                : null;
            $requesterWhatsapp = in_array($validated['communication_preference'], ['whatsapp', 'both'])
                ? ($request->input('whatsapp_number') ?? null)
                : null;

            // Normalize phone number to digits only
            $normalizedWhatsapp = static::normalizePhoneNumber($requesterWhatsapp);

            // Smart duplicate detection and staff management
            $existingStaff = $client->staff_information ?? [];
            $isDuplicate = false;

            foreach ($existingStaff as $staff) {
                $existingName = $staff['staff_name'] ?? null;
                $existingEmail = $staff['staff_email'] ?? null;
                $existingContact = $staff['staff_contact_number'] ?? null;

                // Name matching (case-insensitive, trimmed)
                $nameMatch = $existingName &&
                             strtolower(trim($existingName)) === strtolower(trim($requesterName));

                // Email matching (case-insensitive, trimmed)
                $emailMatch = $existingEmail && $requesterEmail &&
                              strtolower(trim($existingEmail)) === strtolower(trim($requesterEmail));

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
            if (! $isDuplicate && (! empty($requesterName) || ! empty($normalizedWhatsapp))) {
                // Check if we have existing staff with same name (to update contact info)
                $existingStaffIndex = null;
                foreach ($existingStaff as $index => $staff) {
                    $existingName = $staff['staff_name'] ?? null;
                    if ($existingName && strtolower(trim($existingName)) === strtolower(trim($requesterName))) {
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
                    if (! empty($requesterEmail)) {
                        $existingStaff[$existingStaffIndex]['staff_email'] = trim($requesterEmail);
                    }
                    // Update timestamp to show this contact info was recently verified
                    $existingStaff[$existingStaffIndex]['updated_at'] = now()->toISOString();
                } else {
                    // Create new staff entry
                    $countryCode = static::detectCountryCode($normalizedWhatsapp ?? '');
                    $staffEmail = (! empty($requesterEmail) && ! empty($normalizedWhatsapp)) ? trim($requesterEmail) : null;

                    $newStaff = [
                        'staff_name' => trim($requesterName) ?: null,
                        'staff_email' => $staffEmail,
                        'staff_contact_number' => $normalizedWhatsapp,
                        'staff_contact_number_country' => $countryCode,
                        'added_from' => 'wishlist_tracker',
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
     * Handle requester information processing.
     */
    protected function handleReporterInformation(Project $project, array $validated, Request $request): void
    {
        // Auto-transfer requester information to client's staff_information
        $client = $project->client;
        if ($client) {
            $requesterName = $validated['name'];
            $requesterEmail = in_array($validated['communication_preference'], ['email', 'both'])
                ? ($request->input('email') ?? null)
                : null;
            $requesterWhatsapp = in_array($validated['communication_preference'], ['whatsapp', 'both'])
                ? ($request->input('whatsapp_number') ?? null)
                : null;

            // Normalize phone number to digits only
            $normalizedWhatsapp = static::normalizePhoneNumber($requesterWhatsapp);

            // Smart duplicate detection and staff management
            $existingStaff = $client->staff_information ?? [];
            $isDuplicate = false;

            foreach ($existingStaff as $staff) {
                $existingName = $staff['staff_name'] ?? null;
                $existingEmail = $staff['staff_email'] ?? null;
                $existingContact = $staff['staff_contact_number'] ?? null;

                // Name matching (case-insensitive, trimmed)
                $nameMatch = $existingName &&
                             strtolower(trim($existingName)) === strtolower(trim($requesterName));

                // Email matching (case-insensitive, trimmed)
                $emailMatch = $existingEmail && $requesterEmail &&
                              strtolower(trim($existingEmail)) === strtolower(trim($requesterEmail));

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
            if (! $isDuplicate && (! empty($requesterName) || ! empty($normalizedWhatsapp))) {
                // Check if we have existing staff with same name (to update contact info)
                $existingStaffIndex = null;
                foreach ($existingStaff as $index => $staff) {
                    $existingName = $staff['staff_name'] ?? null;
                    if ($existingName && strtolower(trim($existingName)) === strtolower(trim($requesterName))) {
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
                    if (! empty($requesterEmail)) {
                        $existingStaff[$existingStaffIndex]['staff_email'] = trim($requesterEmail);
                    }
                    // Update timestamp to show this contact info was recently verified
                    $existingStaff[$existingStaffIndex]['updated_at'] = now()->toISOString();
                } else {
                    // Create new staff entry
                    $countryCode = static::detectCountryCode($normalizedWhatsapp ?? '');
                    $staffEmail = (! empty($requesterEmail) && ! empty($normalizedWhatsapp)) ? trim($requesterEmail) : null;

                    $newStaff = [
                        'staff_name' => trim($requesterName) ?: null,
                        'staff_email' => $staffEmail,
                        'staff_contact_number' => $normalizedWhatsapp,
                        'staff_contact_number_country' => $countryCode,
                        'added_from' => 'wishlist_tracker',
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
        return 'Your wishlist item has been submitted successfully. Thank you!';
    }
}
