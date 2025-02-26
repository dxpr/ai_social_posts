/**
 * @file
 * JavaScript behaviors for Hacker News post form.
 */

(function ($, Drupal, once) {
  'use strict';

  /**
   * Behavior for Hacker News post form.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.hackernewsPostForm = {
    attach: function (context, settings) {
      // Initialize the form handlers with appropriate delays to ensure CKEditor is loaded
      once('hackernews-form', 'form.ai-social-post-hackernews-post-form', context).forEach(function (form) {
        // Try immediately and then with increasing delays to ensure CKEditor is fully loaded
        initHackernewsFormHandler(form);
        setTimeout(function() { initHackernewsFormHandler(form); }, 500);
        setTimeout(function() { initHackernewsFormHandler(form); }, 1000);
      });
      
      /**
       * Initialize a Hacker News form handler for a specific form.
       *
       * @param {HTMLElement} form
       *   The form element to initialize.
       */
      function initHackernewsFormHandler(form) {
        const $form = $(form);
        const $topicField = $form.find('input[name="hackernews_topic[0][value]"]');
        
        // Exit early if topic field is not found
        if (!$topicField.length) {
          return;
        }
        
        const $titleField = $form.find('textarea[name="title[0][value]"]');
        const $postField = $form.find('textarea[name="post[0][value]"]');
        
        // Function to update fields when topic changes
        const updateFields = function() {
          const topic = $topicField.val().trim();
          
          if (!topic) {
            return;
          }
          
          // Update textarea values
          updateTextareaValue($titleField, topic);
          updateTextareaValue($postField, topic);
          
          // Update CKEditor 5 instances if available
          updateCKEditor5($titleField, topic);
          updateCKEditor5($postField, topic);
        };
        
        /**
         * Update a textarea value with the Hacker News topic URL.
         *
         * @param {jQuery} $field
         *   The textarea field to update.
         * @param {string} topic
         *   The topic value to use in the URL.
         */
        function updateTextareaValue($field, topic) {
          if (!$field.length) {
            return;
          }
          
          let value = $field.val();
          
          // Replace any existing Hacker News topic URL with the new one
          value = value.replace(
            /https:\/\/hackersearch\.net\/ask\?q=[^\s]+/g,
            `https://hackersearch.net/ask?q=${topic}`
          );
          
          // If no URL exists, add it
          if (!value.includes('https://hackersearch.net/ask?q=')) {
            value += ` https://hackersearch.net/ask?q=${topic}`;
          }
          
          // Update the textarea value
          $field.val(value);
        }
        
        /**
         * Update a CKEditor 5 instance with the Hacker News topic URL.
         *
         * @param {jQuery} $field
         *   The textarea field associated with the CKEditor.
         * @param {string} topic
         *   The topic value to use in the URL.
         */
        function updateCKEditor5($field, topic) {
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
            
            // Replace any existing Hacker News topic URL with the new one
            content = content.replace(
              /https:\/\/hackersearch\.net\/ask\?q=[^\s]+/g,
              `https://hackersearch.net/ask?q=${topic}`
            );
            
            // If no URL exists, add it
            if (!content.includes('https://hackersearch.net/ask?q=')) {
              // If content ends with a paragraph, add to that paragraph
              if (content.endsWith('</p>')) {
                content = content.replace(
                  /<\/p>$/,
                  ` https://hackersearch.net/ask?q=${topic}</p>`
                );
              } else {
                // Otherwise add a new paragraph
                content += `<p>https://hackersearch.net/ask?q=${topic}</p>`;
              }
            }
            
            // Update the editor content
            editor.setData(content);
          }
        }
        
        // Monitor the topic field for changes
        $topicField.on('change keyup', updateFields);
        
        // Initial update in case the field is pre-filled
        updateFields();
      }
    }
  };
})(jQuery, Drupal, once); 