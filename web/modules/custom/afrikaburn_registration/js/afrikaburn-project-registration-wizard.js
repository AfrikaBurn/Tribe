
/**
 * @file
 * AfrikaBurn registration wizard behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnRegistrationWizard = {
    attach: function (context, settings) {
      if (context == document){


        /* ---- Ready ---- */


        // Check selections on next click
        $('#afrikaburn-registration-wizard details#ready .js-next').mousedown(
          (event) => {
            checkProjectTodo()
            checkNewProjectType()
            checkProjectSelection()
          }
        )
        $('#afrikaburn-registration-wizard [name="new_project[type]"]').change(() => checkNewProjectType())
        $('#afrikaburn-registration-wizard [name="existing_project[project]"]').change(() => checkProjectSelection())


        // Cascade new project to new collective
        $('#afrikaburn-registration-wizard input[name="project_todo"]').change(
          () => {
            setTimeout(() => $('#afrikaburn-registration-wizard input[name="collective_todo"]').change(), 10)
            checkProjectTodo()
          }
        )
        $('#afrikaburn-registration-wizard #edit-new-project-title').change(
          function(){
            $('#edit-new-collective-name').val('The ' + $(this).val() + ' Collective')
          }
        )
        $('#afrikaburn-registration-wizard #edit-new-project-description').change(
          function(){
            $('#edit-new-collective-description').val($(this).val())
          }
        )

        // Cascade existing project selection to existing collective selection
        $('input[name="existing_project[project]"]').change(
          function() {

            var cid = $(this).next().find('.collective').data('cid')

            $('input[name="existing_collective[collective]"][value="' + cid + '"]').attr(
              'checked', 'checked'
            )
          }
        )


        /* ---- Steady ---- */


        // Check selections on next click
        $('#afrikaburn-registration-wizard details#steady .js-next').mousedown(
          (event) => {
            checkCollectiveTodo()
            checkCollectiveSelection()
          }
        )
        $('#afrikaburn-registration-wizard [name="collective_todo"]').change(() => checkCollectiveTodo())
        $('#afrikaburn-registration-wizard [name="existing_collective[collective]"]').change(() => checkCollectiveSelection())


        /* ---- Validation ---- */


        function checkError(condition, name){
          if (condition && $('[name="' + name + '"]').is(':visible')){
            $('[name="' + name + '_error"]').show()
            $('#afrikaburn-registration-wizard [name="' + name + '"]').addClass('error')
          } else {
            $('[name="' + name + '_error"]').hide()
            $('#afrikaburn-registration-wizard [name="' + name + '"]')
              .removeClass('error')
              .siblings('.form-item--error-message').hide()

          }
        }

        /* -- Ready -- */

        function checkProjectTodo(){
          checkError($('[name="project_todo"]:checked').val() == undefined, 'project_todo')
        }
        function checkNewProjectType(){
          checkError($('[name="new_project[type]"]:checked').val() == undefined, 'new_project[type]')
        }
        function checkProjectSelection(){
          checkError($('[name="existing_project[project]"]:checked').val() == undefined, 'existing_project[project]')
        }

        /* -- Steady -- */

        function checkCollectiveTodo(){
          checkError($('[name="collective_todo"]:checked').val() == undefined, 'collective_todo')
        }
        function checkCollectiveSelection(){
          checkError($('[name="existing_collective[collective]"]:checked').val() == undefined, 'existing_collective[collective]')
        }

        /* -- Go -- */

        $('#afrikaburn-registration-wizard').submit(
          () => {
            checkProjectTodo()
            checkNewProjectType()
            checkProjectSelection()
            checkCollectiveTodo()
            checkCollectiveSelection()

            if ($('#afrikaburn-registration-wizard .error:visible').length) return false;
          }
        )
      }
    }
  }
})(jQuery)
