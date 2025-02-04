# AI Social Posts for Drupal

A Drupal module that provides a content entity type for creating social media posts.

## Features
- Content entity type for AI Social Posts
- Node integration 
- Platform-specific validation
- Fieldable post types

## Supported Platforms
Each platform is provided as a separate submodule:
- X (Twitter)
- LinkedIn
- Facebook 
- Reddit
- Medium
- Bluesky
- Substack

## Requirements
- Drupal 10.3 or 11.0
- Required modules: options, user

## Installation
1. Install using Composer:
    composer require drupal/ai_social_posts

2. Enable base module and desired platform modules:
    drush en ai_social_posts ai_social_posts_x ai_social_posts_linkedin

For platform-specific documentation, see the README in each submodule directory.
