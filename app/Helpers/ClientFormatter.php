<?php

namespace App\Helpers;

class ClientFormatter
{
 /**
  * Format client name with shortening logic
  * Example: "Amirul Shafiq Harun" becomes "Amirul S. H."
  */
 public static function formatClientName(?string $name): string
 {
  if (empty($name)) {
   return '';
  }

  $parts = explode(' ', trim($name));

  // If only one word, return as is
  if (count($parts) === 1) {
   return $parts[0];
  }

  // If two words, return first word + first letter of second word
  if (count($parts) === 2) {
   return $parts[0] . ' ' . substr($parts[1], 0, 1) . '.';
  }

  // If three or more words, return first + middle initial + last initial
  $first = $parts[0];
  $last = end($parts); // Get the last element without removing it
  $middleInitial = '';

  // If there's a middle name, get its first letter
  if (count($parts) >= 3) {
   $middleInitial = substr($parts[1], 0, 1) . '. ';
  }

  return $first . ' ' . $middleInitial . substr($last, 0, 1) . '.';
 }

 /**
  * Format company name with character limit
  * Limits to 10 characters with ellipsis if longer
  */
 public static function formatCompanyName(?string $company): string
 {
  if (empty($company)) {
   return '';
  }

  // If company name is longer than 10 characters, truncate and add ellipsis
  if (strlen($company) > 10) {
   return substr($company, 0, 10) . '...';
  }

  return $company;
 }

 /**
  * Format client display name combining name and company
  * Returns formatted string like "Amirul S. H. (Cheqqme Da...)"
  */
 public static function formatClientDisplay(?string $name, ?string $company): string
 {
  $formattedName = self::formatClientName($name);
  $formattedCompany = self::formatCompanyName($company);

  return "{$formattedName} ({$formattedCompany})";
 }
}
