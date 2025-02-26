/**
 * @file
 * JavaScript behaviors for Medium post form.
 */

(function ($, Drupal, once) {
  'use strict';

  /**
   * Behavior for Medium post form.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.mediumPostForm = {
    attach: function (context, settings) {
      // Initialize the form handlers with appropriate delays to ensure CKEditor is loaded
      once('medium-form', 'form.ai-social-post-medium-post-form', context).forEach(function (form) {
        // Try immediately and then with increasing delays to ensure CKEditor is fully loaded
        initMediumFormHandler(form);
        setTimeout(function() { initMediumFormHandler(form); }, 500);
        setTimeout(function() { initMediumFormHandler(form); }, 1000);
      });
      
      /**
       * Initialize a Medium form handler for a specific form.
       *
       * @param {HTMLElement} form
       *   The form element to initialize.
       */
      function initMediumFormHandler(form) {
        const $form = $(form);
        const $tagField = $form.find('input[name="medium_tag[0][value]"]');
        
        // Exit early if tag field is not found
        if (!$tagField.length) {
          return;
        }
        
        const $titleField = $form.find('textarea[name="title[0][value]"]');
        const $subtitleField = $form.find('textarea[name="subtitle[0][value]"]');
        const $postField = $form.find('textarea[name="post[0][value]"]');
        
        // Function to update fields when tag changes
        const updateFields = function() {
          const tag = $tagField.val().trim();
          
          if (!tag) {
            return;
          }
          
          // Update textarea values
          updateTextareaValue($titleField, tag);
          updateTextareaValue($subtitleField, tag);
          updateTextareaValue($postField, tag);
          
          // Update CKEditor 5 instances if available
          updateCKEditor5($titleField, tag);
          updateCKEditor5($subtitleField, tag);
          updateCKEditor5($postField, tag);
        };
        
        /**
         * Update a textarea value with the Medium tag URL.
         *
         * @param {jQuery} $field
         *   The textarea field to update.
         * @param {string} tag
         *   The tag value to use in the URL.
         */
        function updateTextareaValue($field, tag) {
          if (!$field.length) {
            return;
          }
          
          let value = $field.val();
          
          // Replace any existing Medium tag URL with the new one
          value = value.replace(
            /https:\/\/medium\.com\/feed\/tag\/[^\s]+/g,
            `https://medium.com/feed/tag/${tag}`
          );
          
          // If no URL exists, add it
          if (!value.includes('https://medium.com/feed/tag/')) {
            value += ` https://medium.com/feed/tag/${tag}`;
          }
          
          // Update the textarea value
          $field.val(value);
        }
        
        /**
         * Update a CKEditor 5 instance with the Medium tag URL.
         *
         * @param {jQuery} $field
         *   The textarea field associated with the CKEditor.
         * @param {string} tag
         *   The tag value to use in the URL.
         */
        function updateCKEditor5($field, tag) {
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
            
            // Replace any existing Medium tag URL with the new one
            content = content.replace(
              /https:\/\/medium\.com\/feed\/tag\/[^\s]+/g,
              `https://medium.com/feed/tag/${tag}`
            );
            
            // If no URL exists, add it
            if (!content.includes('https://medium.com/feed/tag/')) {
              // If content ends with a paragraph, add to that paragraph
              if (content.endsWith('</p>')) {
                content = content.replace(
                  /<\/p>$/,
                  ` https://medium.com/feed/tag/${tag}</p>`
                );
              } else {
                // Otherwise add a new paragraph
                content += `<p>https://medium.com/feed/tag/${tag}</p>`;
              }
            }
            
            // Update the editor content
            editor.setData(content);
          }
        }
        
        // Monitor the tag field for changes
        $tagField.on('change keyup', updateFields);
        
        // Initial update in case the field is pre-filled
        updateFields();
      }
    }
  };
})(jQuery, Drupal, once); 