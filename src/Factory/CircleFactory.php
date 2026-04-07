<?php

declare(strict_types=1);

namespace App\Factory;

use App\Circle\Domain\Model\Circle;
use App\DataFixtures\Concern\TimestampBackdateTrait;

final class CircleFactory
{
    use TimestampBackdateTrait;

    /** @var array<array{name: string, slug: string, description: string, visibility: string}> */
    private const CIRCLES = [
        ['name' => 'Powerlifting Pit', 'slug' => 'powerlifting-pit', 'description' => 'Focused on squat, bench, and deadlift techniques. Share PRs and form checks.', 'visibility' => 'public'],
        ['name' => 'The Squat Rack', 'slug' => 'the-squat-rack', 'description' => 'All about leg day. Squat variations, mobility, and lower body development.', 'visibility' => 'public'],
        ['name' => 'HIIT Nation', 'slug' => 'hiit-nation', 'description' => 'High-intensity interval training. Cardio endurance and metabolic conditioning.', 'visibility' => 'public'],
        ['name' => 'Barbell Brotherhood', 'slug' => 'barbell-brotherhood', 'description' => 'Olympic weightlifting discussion. Snatch, clean & jerk, programming.', 'visibility' => 'public'],
        ['name' => 'Keto Gains Kitchen', 'slug' => 'keto-gains-kitchen', 'description' => 'Ketogenic diet for fitness. Macros, recipes, and experiences.', 'visibility' => 'public'],
        ['name' => 'Endurance Engine', 'slug' => 'endurance-engine', 'description' => 'Marathon, half-marathon, and ultra training. Long-distance performance.', 'visibility' => 'public'],
        ['name' => 'Mobility Matters', 'slug' => 'mobility-matters', 'description' => 'Flexibility, range of motion, injury prevention through mobility work.', 'visibility' => 'public'],
        ['name' => 'CrossFit Collective', 'slug' => 'crossfit-collective', 'description' => 'CrossFit workouts, scaling options, and community events.', 'visibility' => 'public'],
        ['name' => 'Running Rabbits', 'slug' => 'running-rabbits', 'description' => 'Road running discussion. 5K, 10K, and sprint training.', 'visibility' => 'public'],
        ['name' => 'Yoga for Athletes', 'slug' => 'yoga-for-athletes', 'description' => 'Yoga practices to enhance athletic performance and recovery.', 'visibility' => 'public'],
        ['name' => 'Calisthenics Core', 'slug' => 'calisthenics-core', 'description' => 'Bodyweight training. Pull-ups, push-ups, handstands, and skills.', 'visibility' => 'public'],
        ['name' => 'Olympic Weightlifting', 'slug' => 'olympic-weightlifting', 'description' => 'Snatch and clean & jerk technique, training programs, competitions.', 'visibility' => 'public'],
        ['name' => 'Strongman Arena', 'slug' => 'strongman-arena', 'description' => 'Strongman competitions and training. Atlas stones, logs, farmers carry.', 'visibility' => 'public'],
        ['name' => 'Nutrition Nerds', 'slug' => 'nutrition-nerds', 'description' => 'Science-backed nutrition advice. Macros, micronutrients, supplements.', 'visibility' => 'public'],
        ['name' => 'Recovery Room', 'slug' => 'recovery-room', 'description' => 'Sleep, stretching, massage, and active recovery techniques.', 'visibility' => 'public'],
        ['name' => 'Bulk Season', 'slug' => 'bulk-season', 'description' => 'Caloric surplus training and nutrition. Gaining muscle mass safely.', 'visibility' => 'public'],
        ['name' => 'Cut Season', 'slug' => 'cut-season', 'description' => 'Fat loss while preserving muscle. Caloric deficit strategies.', 'visibility' => 'public'],
        ['name' => '5K Prep', 'slug' => '5k-prep', 'description' => '5K race training. Interval workouts, pacing, and race strategy.', 'visibility' => 'public'],
        ['name' => 'Marathon Madness', 'slug' => 'marathon-madness', 'description' => 'Full marathon training. Long runs, taper, race day preparation.', 'visibility' => 'public'],
        ['name' => 'Sleep & Recovery', 'slug' => 'sleep-recovery', 'description' => 'Optimizing sleep quality and sleep duration for fitness gains.', 'visibility' => 'public'],
        ['name' => 'Boxing Basics', 'slug' => 'boxing-basics', 'description' => 'Boxing techniques, combinations, and footwork. Combat fitness training.', 'visibility' => 'public'],
        ['name' => 'Swimming Strength', 'slug' => 'swimming-strength', 'description' => 'Swimming training, pool workouts, and technique improvement.', 'visibility' => 'public'],
        ['name' => 'Cycling Chronicles', 'slug' => 'cycling-chronicles', 'description' => 'Road cycling, mountain biking, and bike fitness.', 'visibility' => 'public'],
        ['name' => 'Functional Fitness', 'slug' => 'functional-fitness', 'description' => 'Functional movement patterns and practical fitness.', 'visibility' => 'public'],
        ['name' => 'Mental Health Matters', 'slug' => 'mental-health-matters', 'description' => 'Mental health, fitness psychology, and mindfulness for athletes.', 'visibility' => 'public'],
        ['name' => 'Core Strength', 'slug' => 'core-strength', 'description' => 'Abs, core stability, and central nervous system training.', 'visibility' => 'public'],
        ['name' => 'Gym Equipment Talk', 'slug' => 'gym-equipment-talk', 'description' => 'Reviews, setup advice, and equipment recommendations.', 'visibility' => 'public'],
        ['name' => 'Bodybuilding Basics', 'slug' => 'bodybuilding-basics', 'description' => 'Hypertrophy training, posing, and competition preparation.', 'visibility' => 'public'],
        ['name' => 'Stretching Science', 'slug' => 'stretching-science', 'description' => 'Static stretching, dynamic stretching, and flexibility protocols.', 'visibility' => 'public'],
        ['name' => 'Injury Prevention', 'slug' => 'injury-prevention', 'description' => 'Prehab, rehab, and staying healthy long-term.', 'visibility' => 'public'],
        ['name' => 'Plyometrics Power', 'slug' => 'plyometrics-power', 'description' => 'Explosive movements and power development training.', 'visibility' => 'public'],
        ['name' => 'Rowing Rhythm', 'slug' => 'rowing-rhythm', 'description' => 'Rowing machine training, rowing technique, and water rowing.', 'visibility' => 'public'],
        ['name' => 'Tennis Fitness', 'slug' => 'tennis-fitness', 'description' => 'On-court conditioning and lateral movement training.', 'visibility' => 'public'],
        ['name' => 'Basketball Training', 'slug' => 'basketball-training', 'description' => 'Basketball-specific drills, vertical jump, and court fitness.', 'visibility' => 'public'],
        ['name' => 'Soccer Conditioning', 'slug' => 'soccer-conditioning', 'description' => 'Soccer fitness, agility, and intermittent sprint training.', 'visibility' => 'public'],
        ['name' => 'Sparring & Combat', 'slug' => 'sparring-combat', 'description' => 'Sparring, grappling, and combat sport training.', 'visibility' => 'public'],
        ['name' => 'Rope Skipping Skills', 'slug' => 'rope-skipping-skills', 'description' => 'Jump rope techniques, double unders, and speed rope training.', 'visibility' => 'public'],
        ['name' => 'Deadlift Dynasty', 'slug' => 'deadlift-dynasty', 'description' => 'Deadlift variations, technique, and max effort training.', 'visibility' => 'public'],
        ['name' => 'Bench Press Brotherhood', 'slug' => 'bench-press-brotherhood', 'description' => 'Bench press variations, grip width, and chest development.', 'visibility' => 'public'],
        ['name' => 'Isolation Exercises', 'slug' => 'isolation-exercises', 'description' => 'Single-joint movements for targeted muscle development.', 'visibility' => 'public'],
        ['name' => 'Compound Movement Masters', 'slug' => 'compound-movement-masters', 'description' => 'Multi-joint exercises and full-body training efficiency.', 'visibility' => 'public'],
        ['name' => 'Ab Challenge', 'slug' => 'ab-challenge', 'description' => 'Core training progressions and six-pack development.', 'visibility' => 'public'],
        ['name' => 'Back Gains', 'slug' => 'back-gains', 'description' => 'Back development, pull-ups, rows, and lat activation.', 'visibility' => 'public'],
        ['name' => 'Shoulder Shredder', 'slug' => 'shoulder-shredder', 'description' => 'Shoulder training, overhead pressing, and raise variations.', 'visibility' => 'public'],
        ['name' => 'Arm Anatomy', 'slug' => 'arm-anatomy', 'description' => 'Bicep and tricep training for impressive arm size.', 'visibility' => 'public'],
        ['name' => 'Leg Day Legends', 'slug' => 'leg-day-legends', 'description' => 'Comprehensive leg training. Quads, hamstrings, calves.', 'visibility' => 'public'],
        ['name' => 'Chest Champion', 'slug' => 'chest-champion', 'description' => 'Chest training, pec development, and pressing variations.', 'visibility' => 'public'],
        ['name' => 'Glute Activation', 'slug' => 'glute-activation', 'description' => 'Glute development and hip extension focus.', 'visibility' => 'public'],
        ['name' => 'Cardio Conditioning', 'slug' => 'cardio-conditioning', 'description' => 'Steady-state cardio, interval training, and cardiovascular health.', 'visibility' => 'public'],
        ['name' => 'Weight Loss Warrior', 'slug' => 'weight-loss-warrior', 'description' => 'Sustainable weight loss strategies and lifestyle changes.', 'visibility' => 'public'],
        ['name' => 'Muscle Gaining Mastery', 'slug' => 'muscle-gaining-mastery', 'description' => 'Hypertrophy science and systematic muscle building.', 'visibility' => 'public'],
        ['name' => 'Performance Enhancement', 'slug' => 'performance-enhancement', 'description' => 'Sport-specific performance training and athletic development.', 'visibility' => 'public'],
        ['name' => 'Strength Training Science', 'slug' => 'strength-training-science', 'description' => 'Evidence-based strength training programming and periodization.', 'visibility' => 'public'],
        ['name' => 'Mobility Enhancement', 'slug' => 'mobility-enhancement', 'description' => 'Advanced mobility techniques and movement optimization.', 'visibility' => 'public'],
        ['name' => 'Form & Technique', 'slug' => 'form-technique', 'description' => 'Perfect form analysis and proper technique execution.', 'visibility' => 'public'],
        ['name' => 'Progressive Overload', 'slug' => 'progressive-overload', 'description' => 'Progressive overload strategies for continuous improvement.', 'visibility' => 'public'],
        ['name' => 'Training Splits', 'slug' => 'training-splits', 'description' => 'Different training splits: PPL, UL, Full-Body, etc.', 'visibility' => 'public'],
        ['name' => 'Workout Programming', 'slug' => 'workout-programming', 'description' => 'Program design, periodization, and structured training.', 'visibility' => 'public'],
        ['name' => 'Supplement Science', 'slug' => 'supplement-science', 'description' => 'Effective supplements, myths vs facts, and supplementation strategy.', 'visibility' => 'public'],
        ['name' => 'Macro Counting', 'slug' => 'macro-counting', 'description' => 'Tracking macronutrients and flexible dieting approach.', 'visibility' => 'public'],
        ['name' => 'Meal Prep Central', 'slug' => 'meal-prep-central', 'description' => 'Meal planning and batch cooking for fitness goals.', 'visibility' => 'public'],
        ['name' => 'Energy Systems', 'slug' => 'energy-systems', 'description' => 'Understanding ATP, glycolytic, and oxidative systems.', 'visibility' => 'public'],
        ['name' => 'Fitness Gear Reviews', 'slug' => 'fitness-gear-reviews', 'description' => 'Honest reviews of fitness equipment and apparel.', 'visibility' => 'public'],
        ['name' => 'Home Gym Heroes', 'slug' => 'home-gym-heroes', 'description' => 'Building effective home gyms and minimal equipment training.', 'visibility' => 'public'],
        ['name' => 'Competition Corner', 'slug' => 'competition-corner', 'description' => 'Sports competition discussion and event announcements.', 'visibility' => 'public'],
        ['name' => 'Fitness Goals', 'slug' => 'fitness-goals', 'description' => 'Goal setting, tracking progress, and accountability.', 'visibility' => 'public'],
        ['name' => 'Motivation Station', 'slug' => 'motivation-station', 'description' => 'Inspirational stories and fitness motivation.', 'visibility' => 'public'],
        ['name' => 'Transformation Tales', 'slug' => 'transformation-tales', 'description' => 'Before/after transformations and success stories.', 'visibility' => 'public'],
        ['name' => 'Newbie Gains', 'slug' => 'newbie-gains', 'description' => 'Beginners welcome! Start your fitness journey here.', 'visibility' => 'public'],
        ['name' => 'Advanced Training', 'slug' => 'advanced-training', 'description' => 'Advanced programming, periodization, and elite training.', 'visibility' => 'public'],
        ['name' => 'Genetic Lottery', 'slug' => 'genetic-lottery', 'description' => 'Genetics in fitness, leveraging natural strengths.', 'visibility' => 'public'],
        ['name' => 'Lifestyle & Wellness', 'slug' => 'lifestyle-wellness', 'description' => 'Holistic health, stress management, and overall wellness.', 'visibility' => 'public'],
        ['name' => 'Aging Gracefully', 'slug' => 'aging-gracefully', 'description' => 'Fitness for older adults and longevity training.', 'visibility' => 'public'],
        ['name' => 'Women in Fitness', 'slug' => 'women-in-fitness', 'description' => 'Women-focused fitness, hormones, and specific challenges.', 'visibility' => 'public'],
        ['name' => 'Men\'s Health & Fitness', 'slug' => 'mens-health-fitness', 'description' => 'Men-specific health topics and fitness discussion.', 'visibility' => 'public'],
        ['name' => 'Pregnancy & Postpartum', 'slug' => 'pregnancy-postpartum', 'description' => 'Pre- and post-pregnancy fitness guidance.', 'visibility' => 'public'],
        ['name' => 'Teen Fitness', 'slug' => 'teen-fitness', 'description' => 'Safe and effective fitness training for teenagers.', 'visibility' => 'public'],
        ['name' => 'Fitness for Kids', 'slug' => 'fitness-for-kids', 'description' => 'Getting children active and building healthy habits.', 'visibility' => 'public'],
        ['name' => 'Adaptive Fitness', 'slug' => 'adaptive-fitness', 'description' => 'Fitness adapted for different abilities and disabilities.', 'visibility' => 'public'],
        ['name' => 'Sport Medicine', 'slug' => 'sport-medicine', 'description' => 'Sports medicine insights and injury rehabilitation.', 'visibility' => 'public'],
        ['name' => 'Physical Therapy', 'slug' => 'physical-therapy', 'description' => 'Physical therapy exercises and rehabilitation protocols.', 'visibility' => 'public'],
        ['name' => 'Chiropractic Care', 'slug' => 'chiropractic-care', 'description' => 'Chiropractic perspectives on fitness and alignment.', 'visibility' => 'public'],
        ['name' => 'Nutrition Science', 'slug' => 'nutrition-science', 'description' => 'Deep dive into nutritional biochemistry and applied nutrition.', 'visibility' => 'public'],
        ['name' => 'Vegan Fitness', 'slug' => 'vegan-fitness', 'description' => 'Plant-based nutrition and vegan athlete discussion.', 'visibility' => 'public'],
        ['name' => 'Intermittent Fasting', 'slug' => 'intermittent-fasting', 'description' => 'IF protocols, benefits, and implementation for athletes.', 'visibility' => 'public'],
        ['name' => 'Hydration Mastery', 'slug' => 'hydration-mastery', 'description' => 'Optimal hydration strategies for different activities.', 'visibility' => 'public'],
        ['name' => 'Pre-Workout Optimization', 'slug' => 'pre-workout-optimization', 'description' => 'Pre-workout nutrition and preparation strategies.', 'visibility' => 'public'],
        ['name' => 'Post-Workout Recovery', 'slug' => 'post-workout-recovery', 'description' => 'Post-workout nutrition and recovery optimization.', 'visibility' => 'public'],
        ['name' => 'Sleep Optimization', 'slug' => 'sleep-optimization', 'description' => 'Advanced sleep hacks for recovery and performance.', 'visibility' => 'public'],
        ['name' => 'Stress Management', 'slug' => 'stress-management', 'description' => 'Managing stress for optimal fitness and health outcomes.', 'visibility' => 'public'],
        ['name' => 'Cold Exposure', 'slug' => 'cold-exposure', 'description' => 'Cold therapy, cryotherapy, and cold exposure benefits.', 'visibility' => 'public'],
        ['name' => 'Heat Training', 'slug' => 'heat-training', 'description' => 'Heat therapy, saunas, and heat adaptation training.', 'visibility' => 'public'],
        ['name' => 'Biohacking Body', 'slug' => 'biohacking-body', 'description' => 'Biohacking techniques for fitness optimization.', 'visibility' => 'public'],
        ['name' => 'Fitness Tech', 'slug' => 'fitness-tech', 'description' => 'Fitness trackers, wearables, and tech for athletes.', 'visibility' => 'public'],
        ['name' => 'Crossover Training', 'slug' => 'crossover-training', 'description' => 'Cross-training and sport specificity.', 'visibility' => 'public'],
        ['name' => 'Community Challenge', 'slug' => 'community-challenge', 'description' => 'Community fitness challenges and group events.', 'visibility' => 'public'],
        ['name' => 'Fitness Marketplace', 'slug' => 'fitness-marketplace', 'description' => 'Buy, sell, and trade fitness equipment and programs.', 'visibility' => 'public'],
        ['name' => 'Local Events', 'slug' => 'local-events', 'description' => 'Find and discuss local fitness events and meetups.', 'visibility' => 'public'],
        ['name' => 'Offline Gym Life', 'slug' => 'offline-gym-life', 'description' => 'Appreciation for traditional gym culture and camaraderie.', 'visibility' => 'public'],
        ['name' => 'Fitness Trends', 'slug' => 'fitness-trends', 'description' => 'Latest fitness trends, workouts, and training methodologies.', 'visibility' => 'public'],
    ];

    /**
     * @param string $creatorId The MongoDB ObjectId of the circle creator (User)
     * @return Circle[]
     */
    public function createAll(string $creatorId): array
    {
        $circles = [];

        foreach (self::CIRCLES as $data) {
            $createdAt = $this->randomPastDate(180);

            $circle = new Circle(
                name: $data['name'],
                slug: $data['slug'],
                description: $data['description'],
                visibility: $data['visibility'],
                creatorId: $creatorId,
                moderatorIds: []
            );

            // Backdate the timestamps
            $circle = $this->buildViaReflection(Circle::class, [
                'name' => $circle->getName(),
                'slug' => $circle->getSlug(),
                'description' => $circle->getDescription(),
                'visibility' => $circle->getVisibility(),
                'creatorId' => $circle->getCreatorId(),
                'moderatorIds' => $circle->getModeratorIds(),
                'createdAt' => $createdAt,
                'updatedAt' => $createdAt->modify('+' . random_int(1, 168) . ' hours'),
            ]);

            $circles[] = $circle;
        }

        return $circles;
    }
}
