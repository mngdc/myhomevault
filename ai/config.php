<?php
// ⚙️ Gemini API Configuration
// Make sure this is your valid API key from Google AI Studio
$GEMINI_API_KEY = "AIzaSyDe-JOesS4NnsGAbkdyZn2FddARG_K4H4w"; 

// ✅ Safe internet connection check
// Wrapped in a function_exists() to prevent redeclaration errors
if (!function_exists('is_online')) {
    function is_online() {
        $connected = @fsockopen("www.google.com", 80);
        if ($connected) {
            fclose($connected);
            return true;
        }
        return false;
    }
}
?>
