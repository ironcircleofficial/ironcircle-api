<?php

declare(strict_types=1);

namespace App\Factory;

use App\DataFixtures\Concern\TimestampBackdateTrait;
use App\Post\Domain\Model\Post;

final class PostFactory
{
    use TimestampBackdateTrait;

    /** @var array<string> */
    private const POST_TITLES = [
        'New PR on deadlift - form check inside',
        'Best pre-workout meals under 500 calories',
        'When to deload? Signs your CNS is fried',
        'Finally nailed my first muscle-up after 6 months',
        'Is creatine worth it for endurance athletes?',
        'Comparing RPE vs percentage-based programming',
        'My journey from couch potato to 5K runner',
        'Macro cycling for fat loss without muscle loss',
        'Stretching routine that actually improved my squat',
        'Recovery week checklist - what do you do?',
        'Dealing with shoulder impingement: what worked for me',
        'Optimal protein timing myth or fact?',
        'How to program for both strength and hypertrophy',
        'Intermittent fasting and athletic performance',
        'Sleep tracking experiment: 30 days of optimization',
        'Overcoming gym anxiety as a beginner',
        'My transition from CrossFit to powerlifting',
        'Effective core work beyond just planks',
        'Running on a treadmill vs outdoors',
        'Climbing stairs for glute activation?',
        'What supplements actually work? Evidence review',
        'Programming for natural lifters: periodization mistakes',
        'Mobility work that transformed my squat depth',
        'Bodyweight to barbell progression guide',
        'Handling training around a desk job',
        'Morning vs evening workouts: does timing matter?',
        'How I fixed my form through video analysis',
        'Dealing with gym intimidation as a woman',
        'My experience with daily undulating periodization',
        'Nutrition for recovery after intense training',
        'Setting realistic goals: avoiding the all-or-nothing trap',
        'Building a home gym on a budget',
        'Comparing different running shoes for injury prevention',
        'Yoga for lifters: is it actually useful?',
        'Programming mistakes that delayed my progress',
        'How to structure a training week for multiple sports',
        'Strength gains on a caloric deficit: possible?',
        'My first powerlifting meet experience',
        'Dealing with joint pain: rehab vs pushing through',
        'Nutrition myths that held me back',
        'Training around injuries effectively',
        'Comparing gym memberships vs home gym cost',
        'Explosive training for endurance athletes',
        'How to find your ideal training frequency',
        'Reverse dieting after a cut',
        'Glycogen loading before competition',
        'Mental toughness training for athletes',
        'Periodizing accessory work for size gains',
        'How to structure a deload week',
        'Building mental resilience through fitness',
        'My experience with block periodization',
        'Nutrition mistakes beginners make',
        'How often should I test my maxes?',
        'Comparing different cardio modalities',
        'Building explosive power for sports',
        'Stretching vs foam rolling: when to use each',
        'How to handle training while traveling',
        'Programming for athletes over 40',
        'Core training: not just abs',
        'How to track progress beyond the scale',
        'Sustainable training for busy professionals',
        'Building aerobic base for endurance sports',
        'Managing training fatigue effectively',
        'How to structure your first training program',
        'Comparing bench press variations for chest development',
        'Building grip strength systematically',
        'How to improve your squat depth',
        'Tricep training for bigger arms',
        'Bicep curl variations: which are most effective?',
        'Back training for posture improvement',
        'Shoulder stability work: often overlooked',
        'Glute activation before leg day',
        'Effective calf training methods',
        'Core engagement during lifting',
        'Hip mobility for better squats',
        'Wrist strength and forearm development',
        'Neck training for athletes',
        'Pelvic floor exercises for strength',
        'Working around lower back issues',
        'Improving ankle mobility for deep squats',
        'Knee health in loaded movements',
        'Elbow pain: prehab versus treatment',
        'Training volume recommendations by goal',
        'Rest pause sets: effective or dangerous?',
        'Supersets for efficiency and gains',
        'Drop sets for muscle growth',
        'Tempo training for hypertrophy',
        'Occlusion training without expensive equipment',
        'Isometric holds for strength gains',
        'Eccentric training for muscle building',
        'Cluster sets: practical applications',
        'Wave loading for strength progression',
        'Autoregulation in strength training',
        'RPE vs RIR: which is better?',
        'Monitoring readiness for optimal training',
        'Sleep deprivation impact on performance',
        'Hydration strategies for athletic performance',
        'Caffeine timing for strength training',
        'Beta alanine supplementation effectiveness',
        'BCAAs: science or marketing?',
        'Protein powder quality comparison',
        'Meal frequency: does it matter?',
        'Carb cycling for body composition',
        'Fat adaptation for endurance',
        'Alcohol consumption and muscle gains',
        'Sugar timing around workouts',
        'Micronutrient deficiencies in athletes',
        'Iron levels and endurance performance',
        'Vitamin D and athletic performance',
        'Magnesium for recovery and sleep',
        'Zinc for hormonal health',
        'Electrolytes for hydration',
        'Fiber intake for digestive health',
        'Digestive enzymes: useful or unnecessary?',
        'Probiotics for gut health',
        'Detox myths in fitness',
        'Intermittent fasting protocols',
        'Carnivore diet for athletes',
        'Macro ratios for different goals',
        'Calorie surplus size for muscle gain',
        'Calorie deficit rate for fat loss',
        'Maintenance calories: how to calculate',
        'Reverse engineering diet from body composition',
        'Food quality vs calorie quantity',
        'Flexible dieting vs strict meal plans',
        'Meal prep: time investment worth it?',
        'Eating out while maintaining macros',
        'Handling social events on a diet',
        'Managing hunger on a deficit',
        'Building sustainable eating habits',
        'Recovery modalities ranked by evidence',
        'Contrast water therapy benefits',
        'Compression garments: practical benefits',
        'Massage for recovery: foam roller vs massage gun',
        'Ice baths vs heat therapy',
        'Stretching duration for flexibility',
        'Active recovery days: what to do',
        'Breathing techniques for relaxation',
        'Sauna for recovery and health',
        'Meditation for mental recovery',
        'Journaling for performance tracking',
    ];

    /** @var array<string> */
    private const POST_CONTENTS = [
        'Just hit a new deadlift PR! Would love feedback on form. Video below shows the last two reps. Felt really solid coming off the floor but curious if the lockout could be improved.',
        'After years of trying different pre-workout foods, I\'ve found the perfect combination that doesn\'t upset my stomach. Brown rice with chicken breast 90 minutes before training works every time.',
        'I\'ve been training hard for 8 weeks straight and my lifts are stalling. Looking at my sleep logs, I\'ve been averaging 5-6 hours. Could this be the reason? Any tips for improving sleep quality?',
        'This is what 6 months of consistent training looks like. First muscle-up! Starting point was struggling with pull-ups. Progressive overload and patience really do work.',
        'Looking at endurance athletes, many don\'t take creatine because they think it\'s only for strength. But the research shows benefits for endurance and recovery too. What\'s your experience?',
        'I\'ve been programming for 2 years using RPE and just switched to percentages. Finding it harder to adjust when I\'m fatigued vs hitting %s on paper. Hybrid approach anyone?',
        'After 10 years of sedentary office work, I started running last January. Just completed a 5K race! Never thought this would be possible. Biggest advice: start slowly, listen to your body.',
        'Tired of losing muscle on a cut. Started cycling macros: 200P/250C/60F on training days and 200P/150C/100F on rest days. Two weeks in and feeling good with energy levels.',
        'For years my squat depth was limited. Been doing this specific stretching routine 5x per week and my mobility improved dramatically in just 4 weeks. Sharing the routine in the comments.',
        'Anyone else take a full deload week every 6-8 weeks? I used to think it was wasting time but my strength actually jumped higher after deloading. The recovery is real.',
        'Been dealing with shoulder impingement for months. PT exercises, reducing bench volume, and switching to neutral grip work has been life-changing. Recovery is 80% better.',
        'The timing of protein seems less important than total daily intake, but I notice I recover better with whey right after training. Is this placebo or is there something to it?',
        'Trying to build both strength and size. Been using 6x3 on main lifts and 12x3 on accessories. Progression is slower on strength but muscle gains are solid. Is this the optimal split?',
        'Started intermittent fasting as an endurance athlete. Energy during hard workouts is excellent but I\'m noticing slightly slower recovery. Interesting tradeoff to manage.',
        'Did a month-long sleep tracking experiment. Averaging 8.5+ hours per night resulted in better recovery, faster times, and improved mood. Sleep is the ultimate hack.',
        'Just joined a gym after using my home setup. Intimidating at first but everyone is focused on themselves. First day down, many more to go. If anyone else is nervous, trust the process.',
        'Moved from CrossFit to powerlifting 6 months ago. The intensity is completely different. Recovery time, programming, and mindset shift have been interesting adjustments.',
        'Have been doing just planks for core work forever. Added pallof presses, dead bugs, and anti-rotation work. Core engagement during lifts is noticeably better now.',
        'Running on treadmill for winter training but missing the outdoor feel. Incline running helps but it\'s just not the same. Can\'t wait for spring.',
        'Someone mentioned stair climbing for glute activation. Added 2 sets of stairs twice weekly. Glutes are sore in the best way and my squat lockout improved.',
        'Reviewed the research on popular supplements. Most overhyped but creatine and beta-alanine have solid evidence. Everything else is marginal at best.',
        'Made every periodization mistake in the book for my first 2 years training. Now I understand why progressive overload within rep ranges matters so much.',
        'This specific 3-move mobility sequence transformed my squat. Doing it daily even on off-days. Hip mobility is so underrated for lower body strength.',
        'Created a bodyweight to barbell progression guide based on my experience. Took 6 months from first push-up to barbell bench. Patience is key.',
        'Working a desk job is the worst. Implemented walking meetings, desk stretches, and morning training. Helped manage stiffness and maintain gains.',
        'Morning workouts vs evening training. I used to be an evening person but flipped to mornings for consistency. Strength is similar but mental health improved.',
        'Recorded all my lifts and watched the videos. Form issues I couldn\'t feel in the moment became obvious on playback. Video analysis is a game changer.',
        'As a woman lifting heavy, the intimidation factor was real. First month was rough but now I\'m one of the strongest in my gym. Confidence comes with time.',
        'Tried daily undulating periodization for a full cycle. Varied rep ranges kept training fresh and mentally stimulating. Strength gains were solid.',
        'Post-workout nutrition has been my biggest lever for recovery. Carbs + protein within 30 minutes of training makes a noticeable difference.',
        'Set a goal to bench 225 and hit it! Biggest lesson: break long-term goals into monthly targets. Makes the journey feel less overwhelming.',
        'Built a solid home gym for under $2000. No excuses now about gym access. Consistency beats fancy equipment every time.',
        'Been through three pairs of running shoes and finally found one that clicks. Injury prevention starts with the right footwear.',
        'As a lifter, skeptical of yoga but tried it anyway. Mobility improvements and mental relaxation have become non-negotiable now.',
        'I made every programming mistake possible and wish I knew better. Here are the biggest errors I see in others: too much volume, too much variety, inconsistency.',
        'Training for basketball but need to balance court skills and gym strength. Split my week 3 days gym / 4 days court time. It\'s working well.',
        'Can you build strength on a deficit? The research is clear: yes, especially for beginners. Lean gains are possible with proper nutrition and patience.',
        'Just competed in my first powerlifting meet! The whole experience from meet prep to competition was surreal. Definitely doing it again.',
        'Joint pain is limiting my progress. Being smart about load distribution and rehab has been more effective than just pushing through.',
        'Believed so many nutrition myths starting out. Total intake matters more than meal timing, frequency, or when you eat carbs.',
        'Injured my shoulder but didn\'t stop training. Adjusted exercises to pain-free ranges and the recovery has been surprisingly smooth.',
        'Comparing costs: gym membership $50/month vs home gym one-time $3000. Home gym pays for itself in 5 years but the convenience factor is immense.',
        'Explosive training shouldn\'t be just for sprinters. Endurance athletes benefit from power development for acceleration and finishing kicks.',
        'Finding my training frequency sweet spot took trial and error. Four days per week seems optimal for my recovery capacity.',
        'Started reverse dieting after a 12-week cut. Reintroducing calories slowly prevents rapid fat gain. Currently at maintenance with good energy.',
        'Carb loading before a race isn\'t just about calories. Proper glycogen supercompensation requires timing and specific carb amounts.',
        'Mental toughness is trained like physical strength. Working on being comfortable uncomfortable has improved both my training and life.',
        'Accessory periodization was an aha moment. Progressive overload on accessories, not just main lifts, leads to better long-term gains.',
        'Took a planned deload every 6 weeks. Week looks like: 50% volume, same exercises, focus on movement quality. Jumped back stronger.',
        'Fitness has taught me mental resilience. Facing failure in training made me mentally tougher for life challenges. Best side effect ever.',
        'Just finished my first block periodization cycle. Very different from linear progression. Strength jumped at the end of the block.',
    ];

    /**
     * @param array<string> $authorIds User IDs to randomly select from
     * @param array<string> $circleIds Circle IDs to randomly select from
     * @return Post[]
     */
    public function createAll(array $authorIds, array $circleIds): array
    {
        $posts = [];
        $aiSummaryCount = 0;
        $maxAiSummaries = 15;

        for ($i = 0; $i < 150; $i++) {
            $authorId = $authorIds[array_rand($authorIds)];
            $circleId = $circleIds[array_rand($circleIds)];

            // First 15 posts get AI summary enabled for demonstration
            $aiEnabled = $aiSummaryCount < $maxAiSummaries;
            if ($aiEnabled) {
                $aiSummaryCount++;
            }

            $titleIndex = $i % \count(self::POST_TITLES);
            $contentIndex = $i % \count(self::POST_CONTENTS);

            $createdAt = $this->randomPastDate(180);

            /** @var Post $post */
            $post = $this->buildViaReflection(Post::class, [
                'circleId' => $circleId,
                'authorId' => $authorId,
                'title' => self::POST_TITLES[$titleIndex],
                'content' => self::POST_CONTENTS[$contentIndex],
                'aiSummaryEnabled' => $aiEnabled,
                'createdAt' => $createdAt,
                'updatedAt' => $createdAt->modify('+' . random_int(0, 168) . ' hours'),
            ]);

            $posts[] = $post;
        }

        return $posts;
    }
}
