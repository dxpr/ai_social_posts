/**
 * @file
 * JavaScript behaviors for Reddit post form.
 */

(function ($, Drupal, once) {
  'use strict';

  /**
   * Behavior for Reddit post form.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.redditPostForm = {
    attach: function (context, settings) {
      // Initialize the form handlers with appropriate delays to ensure CKEditor is loaded
      once('reddit-form', 'form.ai-social-post-reddit-post-form', context).forEach(function (form) {
        // Try immediately and then with increasing delays to ensure CKEditor is fully loaded
        initRedditFormHandler(form);
        setTimeout(function() { initRedditFormHandler(form); }, 500);
        setTimeout(function() { initRedditFormHandler(form); }, 1000);
      });
      
      /**
       * Initialize a Reddit form handler for a specific form.
       *
       * @param {HTMLElement} form
       *   The form element to initialize.
       */
      function initRedditFormHandler(form) {
        const $form = $(form);
        const $subredditField = $form.find('input[name="subreddit[0][value]"]');
        
        // Exit early if subreddit field is not found
        if (!$subredditField.length) {
          return;
        }
        
        const $titleField = $form.find('textarea[name="title[0][value]"]');
        const $postField = $form.find('textarea[name="post[0][value]"]');
        
        // Function to update fields when subreddit changes
        const updateFields = function() {
          const subreddit = $subredditField.val().trim();
          
          if (!subreddit) {
            return;
          }
          
          // Update textarea values
          updateTextareaValue($titleField, subreddit);
          updateTextareaValue($postField, subreddit);
          
          // Update CKEditor 5 instances if available
          updateCKEditor5($titleField, subreddit);
          updateCKEditor5($postField, subreddit);
        };
        
        /**
         * Update a textarea value with the Reddit URL.
         *
         * @param {jQuery} $field
         *   The textarea field to update.
         * @param {string} subreddit
         *   The subreddit value to use in the URL.
         */
        function updateTextareaValue($field, subreddit) {
          if (!$field.length) {
            return;
          }
          
          let value = $field.val();
          
          // Replace any existing Reddit URL with the new one
          value = value.replace(
            /https:\/\/www\.reddit\.com\/r\/[^\s]+/g,
            `https://www.reddit.com/r/${subreddit}`
          );
          
          // If no URL exists, add it
          if (!value.includes('https://www.reddit.com/r/')) {
            value += ` https://www.reddit.com/r/${subreddit}`;
          }
          
          // Update the textarea value
          $field.val(value);
        }
        
        /**
         * Update a CKEditor 5 instance with the Reddit URL.
         *
         * @param {jQuery} $field
         *   The textarea field associated with the CKEditor.
         * @param {string} subreddit
         *   The subreddit value to use in the URL.
         */
        function updateCKEditor5($field, subreddit) {
          if (!$field.length) {
            return;
          }
          
          // Get the CKEditor 5 instance ID
          const editorId = $field.attr('data-ckeditor5-id');
          if (!editorId) {
            return;
          }
          
          // Access CKEditor 5 instance through Drupal's API
          if (window.Drupal?.CKEditor5Instances?.has(editorId)) {
            const editor = window.Drupal.CKEditor5Instances.get(editorId);
            let content = editor.getData();
            
            // Replace any existing Reddit URL with the new one
            content = content.replace(
              /https:\/\/www\.reddit\.com\/r\/[^\s]+/g,
              `https://www.reddit.com/r/${subreddit}`
            );
            
            // If no URL exists, add it
            if (!content.includes('https://www.reddit.com/r/')) {
              // If content ends with a paragraph, add to that paragraph
              if (content.endsWith('</p>')) {
                content = content.replace(
                  /<\/p>$/,
                  ` https://www.reddit.com/r/${subreddit}</p>`
                );
              } else {
                // Otherwise add a new paragraph
                content += `<p>https://www.reddit.com/r/${subreddit}</p>`;
              }
            }
            
            // Update the editor content
            editor.setData(content);
          }
        }
        
        // Monitor the subreddit field for changes
        $subredditField.on('change keyup', updateFields);
        
        // Initial update in case the field is pre-filled
        updateFields();
      }
    }
  };
})(jQuery, Drupal, once); 