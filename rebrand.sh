# First find and rename directories only
find . -type d \( -name "*socials*" -o -name "*social-posts*" -o -name "*AiSocialPost*" -o -name "*social_post*" -o -name "*social.post*" -o -name "*social\.post*" -o -name "*Socials*" -o -name "*social_post.*" \) -print | \
  awk -F'/' '{print NF-1 " " $0}' | \
  sort -rn | \
  cut -d' ' -f2- | \
  while IFS= read -r dir; do
    # Process directory path
    newdir="$dir"
    newdir=$(echo "$newdir" | sed 's/socials/ai_social_posts/g')
    newdir=$(echo "$newdir" | sed 's/social-posts/ai-social-posts/g')
    newdir=$(echo "$newdir" | sed 's/AiSocialPost/AiSocialPost/g')
    newdir=$(echo "$newdir" | sed 's/Socials/AiSocialPosts/g')
    newdir=$(echo "$newdir" | sed 's/social_post/ai_social_post/g')
    newdir=$(echo "$newdir" | sed 's/social\.post/ai_social_post/g')
    
    if [ "$dir" != "$newdir" ]; then
      echo "Renaming directory: $dir → $newdir"
      mkdir -p "$(dirname "$newdir")"
      if mv "$dir" "$newdir" 2>/dev/null; then
        echo "Successfully renamed directory"
      else
        echo "Error renaming directory $dir"
      fi
    fi
done

# Then find and rename files
find . -type f \( -name "*socials*" -o -name "*social-posts*" -o -name "*AiSocialPost*" -o -name "*social_post*" -o -name "*social.post*" -o -name "*social\.post*" -o -name "*Socials*" -o -name "*social_post.*" \) -print | \
  awk -F'/' '{print NF-1 " " $0}' | \
  sort -rn | \
  cut -d' ' -f2- | \
  while IFS= read -r file; do
    # Split into directory and filename
    dir=$(dirname "$file")
    filename=$(basename "$file")
    
    # Process filename - handle Drupal config files specifically
    newname="$filename"
    if [[ "$filename" =~ ^core\.entity_form_display\.social_post\. ]]; then
      # Handle the specific case of entity form display config
      newname=$(echo "$filename" | sed 's/\.social_post\./\.ai_social_post\./g')
    elif [[ "$filename" =~ ^field\.field\.social_post\. ]]; then
      # Handle field config files
      newname=$(echo "$filename" | sed 's/\.social_post\./\.ai_social_post\./g')
    elif [[ "$filename" =~ ^field\.storage\.social_post\. ]]; then
      # Handle field storage config files
      newname=$(echo "$filename" | sed 's/\.social_post\./\.ai_social_post\./g')
    else
      # Handle all other cases
      newname=$(echo "$filename" | sed 's/socials/ai_social_posts/g')
      newname=$(echo "$newname" | sed 's/social-posts/ai-social-posts/g')
      newname=$(echo "$newname" | sed 's/AiSocialPost/AiSocialPost/g')
      newname=$(echo "$newname" | sed 's/Socials/AiSocialPosts/g')
      newname=$(echo "$newname" | sed 's/social_post/ai_social_post/g')
      newname=$(echo "$newname" | sed 's/social\.post/ai_social_post/g')
    fi
    
    # Process directory path
    newdir="$dir"
    newdir=$(echo "$newdir" | sed 's/socials/ai_social_posts/g')
    newdir=$(echo "$newdir" | sed 's/social-posts/ai-social-posts/g')
    newdir=$(echo "$newdir" | sed 's/AiSocialPost/AiSocialPost/g')
    newdir=$(echo "$newdir" | sed 's/Socials/AiSocialPosts/g')
    newdir=$(echo "$newdir" | sed 's/social_post/ai_social_post/g')
    newdir=$(echo "$newdir" | sed 's/social\.post/ai_social_post/g')
    
    # Combine directory and filename
    newpath="$newdir/$newname"
    
    if [ -e "$file" ] && [ "$file" != "$newpath" ]; then
      echo "Renaming file: $file → $newpath"
      mkdir -p "$newdir"
      if mv "$file" "$newpath" 2>/dev/null; then
        echo "Successfully renamed file"
      else
        echo "Error renaming $file"
      fi
    fi
done