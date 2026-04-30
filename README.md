> **AI Social Posts** generates platform-optimized social media content from your
> Drupal nodes using AI, supporting 14+ platforms from LinkedIn to TikTok.
> Built by [DXPR](https://dxpr.com).
>
> [Getting Started](https://dxpr.com/c/marketing-cms) |
> [Pricing](https://dxpr.com/pricing) |
> [Try Free Demo](https://try.dxpr.com)

# AI Social Posts: AI-Powered Social Media Content Creation for Drupal

AI-assisted social media content creation across multiple platforms.

## Features

- **Flexible Content Creation**: Create posts from existing content or standalone
- **Platform Integration**: X, LinkedIn, Facebook, Reddit, Medium, Bluesky, Substack, YouTube, Hacker News
- **Interactive AI Editing**: Real-time commands like "Make Longer", "Simplify Language"
- **Extensible Architecture**: Add new platforms via submodules

## Requirements

- Drupal 10.3 or 11.0
- [CKEditor AI Agent](https://www.drupal.org/project/ckeditor_ai_agent)
- [Maxlength](https://www.drupal.org/project/maxlength)

## Installation

```bash
composer require drupal/ai_social_posts
drush en ai_social_posts ai_social_posts_x ai_social_posts_linkedin
```

## Usage

### Primary Method
1. Navigate to any content item
2. Use "AI Social Posts" tab
3. Generate platform-specific variations

### Alternative Method
Create standalone posts at `/admin/content/ai-social-posts`

## Configuration

- Customize AI prompts per platform at configuration screens
- Set character limits and validation rules per platform
- Configure permissions for content creation

## Platform Modules

Each platform requires its submodule:
- `ai_social_posts_x` - X (Twitter)
- `ai_social_posts_linkedin` - LinkedIn
- `ai_social_posts_facebook` - Facebook
- `ai_social_posts_reddit` - Reddit
- `ai_social_posts_medium` - Medium
- `ai_social_posts_bluesky` - Bluesky
- `ai_social_posts_substack` - Substack

## Recommended

- [AI Brand Voice Analyzer](https://www.drupal.org/project/analyze_ai_brand_voice) - Brand consistency
- [AI Sentiment Analyzer](https://www.drupal.org/project/analyze_ai_sentiment) - Content optimization

## Related Modules

- [CKEditor AI Agent](https://www.drupal.org/project/ckeditor_ai_agent) - Required. Powers the interactive AI editing commands inside each post editor
- [Maxlength](https://www.drupal.org/project/maxlength) - Required. Enforces platform-specific character limits (e.g., 300 for Bluesky, 280 for X)
- [Analyze](https://www.drupal.org/project/analyze) - Recommended. Content analysis framework that provides a unified Analyze tab
- [AI Brand Voice Analyzer](https://www.drupal.org/project/analyze_ai_brand_voice) - Recommended. Scores brand voice consistency across your social posts
- [AI Sentiment Analyzer](https://www.drupal.org/project/analyze_ai_sentiment) - Recommended. Multi-dimensional tone analysis for content optimization
- [AI Content Strategy](https://www.drupal.org/project/ai_content_strategy) - Plan what content to create, then use AI Social Posts to promote it