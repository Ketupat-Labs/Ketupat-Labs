<?php
namespace App\Services;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserAchievement;

class AchievementService
{
    /**
     * Get badges available for redemption
     */
    public function getRedeemableBadges(User $user)
    {
        return $user->userBadges()
                    ->where('status', Badge::STATUS_EARNED)
                    ->with('badge')
                    ->get()
                    ->map(function($userBadge) {
                        return [
                            'id' => $userBadge->badge->id,
                            'name' => $userBadge->badge->name,
                            'description' => $userBadge->badge->description,
                            'requirements' => $userBadge->badge->requirements,
                            'status' => $userBadge->status,
                            'earned_date' => $userBadge->earned_date,
                            'user_badge_id' => $userBadge->id
                        ];
                    });
    }

    /**
     * Validate if badge can be redeemed
     */
    public function canRedeemBadge(User $user, $badgeId)
    {
        $userBadge = UserBadge::where('user_id', $user->id)
                             ->where('badge_id', $badgeId)
                             ->first();

        if (!$userBadge) {
            return [
                'can_redeem' => false,
                'message' => 'Badge not found for user'
            ];
        }

        if (!$userBadge->canRedeem()) {
            return [
                'can_redeem' => false,
                'message' => 'Badge is not available for redemption'
            ];
        }

        // Check additional requirements
        $requirementsMet = $this->checkRequirements($userBadge->badge, $user);
        if (!$requirementsMet) {
            return [
                'can_redeem' => false,
                'message' => 'Badge requirements not fully met'
            ];
        }

        return [
            'can_redeem' => true,
            'message' => 'Badge can be redeemed',
            'user_badge' => $userBadge
        ];
    }

    /**
     * Redeem a badge
     */
    public function redeemBadge(User $user, $badgeId)
    {
        $validation = $this->canRedeemBadge($user, $badgeId);
        
        if (!$validation['can_redeem']) {
            return [
                'success' => false,
                'message' => $validation['message']
            ];
        }

        try {
            $userBadge = $validation['user_badge'];
            
            // Update badge status
            $userBadge->redeem();

            // Store in permanent achievement profile
            UserAchievement::create([
                'user_id' => $user->id,
                'badge_id' => $badgeId,
                'redeemed_at' => now()
            ]);

            return [
                'success' => true,
                'message' => 'Badge successfully redeemed!',
                'badge_name' => $userBadge->badge->name,
                'new_status' => Badge::STATUS_REDEEMED
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error redeeming badge: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check badge requirements
     */
    private function checkRequirements(Badge $badge, User $user)
    {
        // Implement your specific requirement checking logic
        // Example: Check if user completed specific lessons, quizzes, etc.
        $requirements = $badge->requirements ?? [];
        
        // Add your requirement validation logic here
        // Return true if all requirements are met, false otherwise
        return true; // Placeholder
    }

    /**
     * Get unmet requirements for error messaging
     */
    public function getUnmetRequirements(Badge $badge, User $user)
    {
        // Implement logic to return specific unmet requirements
        $unmet = [];
        
        // Example requirement checking
        if ($badge->requirements['completed_lessons'] ?? false) {
            $completed = $user->completedLessons()->count();
            $required = $badge->requirements['completed_lessons'];
            if ($completed < $required) {
                $unmet[] = "Complete {$required} lessons (currently {$completed})";
            }
        }

        return $unmet;
    }
}