<?php

declare(strict_types=1);

namespace App\Factory;

use App\DataFixtures\Concern\TimestampBackdateTrait;
use App\Comment\Domain\Model\Comment;

final class CommentFactory
{
    use TimestampBackdateTrait;

    /** @var array<string> */
    private const TOP_LEVEL_COMMENTS = [
        'Great breakdown! I\'ve been struggling with form on this movement.',
        'This is exactly what I needed to hear. Going to try this approach.',
        'Has anyone else experienced similar results? Curious about consistency.',
        'Thanks for sharing! The detailed explanation really helped.',
        'I\'ve tried this before but maybe I was doing it wrong. Will give it another shot.',
        'Mind if I ask about your programming? Looks really effective.',
        'This aligns with what my coach has been telling me. Glad to see the science backs it up.',
        'How long did it take you to see results? Looking for realistic timelines.',
        'The form progression you showed is super helpful. Bookmarking this.',
        'I\'m dealing with the same issue. Did you try any modifications?',
        'That\'s an interesting perspective I hadn\'t considered before.',
        'Definitely going to implement this into my training. Thanks!',
        'The effort you put into this post is appreciated. Very thorough.',
        'Question: how does this scale for different body types?',
        'I started doing this last month and it\'s been game-changing.',
        'The progression looks solid. What\'s your recovery protocol?',
        'Have you tried combining this with other methods? Curious about synergy.',
        'This is the kind of practical advice that actually moves the needle.',
        'The science behind this is fascinating. Any references?',
        'Been lifting for 5 years and I still learned something from this.',
        'Perfect timing. I was just wondering about this exact topic.',
        'The detail level here is impressive. Well worth the read.',
        'Following this protocol starting today. Will report back in a month.',
        'This explains why my previous approach wasn\'t working.',
        'The progression milestones you mentioned are exactly what I need.',
        'Thanks for taking the time to share this knowledge.',
        'Tried a similar approach with great results. Highly recommend.',
        'The mental aspect you mentioned is often overlooked.',
        'This is going straight into my notes for future reference.',
        'How often do you reset and start the progression over?',
        'The level of detail in your explanation is appreciated.',
        'This method seems more sustainable than what I was doing.',
        'Great post! The visual examples really helped me understand.',
        'Saving this for when I plateau. Prevention is key.',
        'The science-backed approach is what I appreciate most here.',
        'Anyone else noticing specific muscle soreness patterns?',
        'I\'ve been doing the basics wrong apparently. Thanks for the clarity.',
        'The systematic approach makes so much sense now.',
        'This is the kind of information that\'s worth its weight in gold.',
        'Curious about your experience with this on a deficit?',
    ];

    /** @var array<string> */
    private const REPLY_COMMENTS = [
        'Great point! I was wondering the same thing.',
        'Have you measured the difference in outcomes?',
        'That\'s a solid variation I hadn\'t considered.',
        'Makes sense. How long did it take to adapt?',
        'Good catch. I was glossing over that detail.',
        'Totally agree. This is the missing piece.',
        'Exactly what I was thinking when I read this.',
        'How do you manage recovery on top of this?',
        'Smart approach. Way more sustainable long-term.',
        'Never thought about it that way before.',
        'That\'s the most practical solution I\'ve heard.',
        'Did you experience any initial adaptation period?',
        'This changes my whole perspective on it.',
        'Perfect explanation. Thanks for clarifying.',
        'The nuance you\'re adding is really valuable.',
        'Same experience here over the past few months.',
        'What percentage improvement did you see?',
        'Appreciate the detailed response.',
        'That\'s a game-changer perspective.',
        'Exactly the information I was looking for.',
        'This should be higher up in the thread.',
        'Your experience aligns with the research.',
        'Great follow-up point that adds depth.',
        'This is the kind of practical wisdom we need.',
        'How long did it take to dial in for you?',
        'Solid breakdown of the nuances involved.',
        'This bridges theory and practice perfectly.',
        'Thanks for the real-world perspective.',
        'The progression you outlined is logical.',
        'Appreciate the alternative perspective here.',
        'This is the missing piece of the puzzle.',
        'Your experience validates the approach.',
        'Great insight that I hadn\'t considered.',
        'The detail here really sets it apart.',
        'This is exactly the clarification needed.',
        'Perfect example of the principle in action.',
        'Appreciate you taking the time to explain.',
        'Your methodology is solid and methodical.',
        'This is the kind of practical advice that works.',
        'Great addition to the conversation.',
    ];

    /**
     * @param array<string> $postIds Post IDs to select from
     * @param array<string> $authorIds User IDs to select from
     * @return Comment[]
     */
    public function createAll(array $postIds, array $authorIds): array
    {
        $comments = [];
        $commentsByPost = array_fill_keys($postIds, []);

        // Create approximately 20 comments per post
        $commentsPerPost = (int) ceil(300 / \count($postIds));

        foreach ($postIds as $postId) {
            $postCommentCount = 0;
            $postComments = [];

            while ($postCommentCount < $commentsPerPost && \count($comments) < 300) {
                $authorId = $authorIds[array_rand($authorIds)];
                $createdAt = $this->randomPastDate(180);

                // 30% of comments are replies to other comments
                $isReply = random_int(1, 10) <= 3;
                $parentCommentId = null;

                if ($isReply && !empty($postComments)) {
                    // Use the object itself since IDs aren't assigned yet
                    $parentComment = $postComments[array_rand($postComments)];
                    // We'll set parent comment reference after persisting
                    $parentCommentId = null; // Will be fixed in fixture
                }

                $commentContent = $isReply
                    ? self::REPLY_COMMENTS[array_rand(self::REPLY_COMMENTS)]
                    : self::TOP_LEVEL_COMMENTS[array_rand(self::TOP_LEVEL_COMMENTS)];

                /** @var Comment $comment */
                $comment = $this->buildViaReflection(Comment::class, [
                    'postId' => $postId,
                    'authorId' => $authorId,
                    'content' => $commentContent,
                    'parentCommentId' => $parentCommentId,
                    'createdAt' => $createdAt,
                    'updatedAt' => $createdAt->modify('+' . random_int(0, 24) . ' hours'),
                ]);

                $comments[] = $comment;
                $postComments[] = $comment;
                $postCommentCount++;
            }

            $commentsByPost[$postId] = $postComments;
        }

        return $comments;
    }
}
