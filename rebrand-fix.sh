# Find all files/directories containing double "ai" patterns and sort by depth
find . \( -name "*ai-ai*" -o -name "*ai_ai*" -o -name "*AiAi*" \) -print | \
  awk -F'/' '{print NF-1 " " $0}' | \
  sort -rn | \
  cut -d' ' -f2- | \
  while IFS= read -r file; do
    # Apply all replacements
    newname="$file"
    newname="${newname//ai-ai/ai}"
    newname="${newname//ai_ai/ai}"
    newname="${newname//AiAi/Ai}"
    
    if [ -e "$file" ] && [ "$file" != "$newname" ]; then
      echo "Fixing: $file → $newname"
      if mv "$file" "$newname"; then
        # Only remove the old file if the move was successful
        rm -f "$file"
      else
        echo "Error fixing $file"
      fi
    fi
done