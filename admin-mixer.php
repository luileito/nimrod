<?php
require_once 'phplibs/class.nimrod.poutil.php';

function n_create_slider($id, $label) {
  $slider  = '<div class="slider-control">';
  $slider .=   '<label for="feat-'.$id.'">'.$label.'</label>';
  $slider .=   '<div class="slider-outer">';
  $slider .=     '<div class="slider-inner">';
  $slider .=       '<div id="slider-'.$id.'" class="slider-bar" data-input-id="feat-'.$id.'" data-feat="'.$id.'"></div>';
  $slider .=     '</div>';
  $slider .=     '<input type="text" value="" id="feat-'.$id.'" name="sort-feats['.$id.']" data-feat="'.$id.'" data-slider-id="slider-'.$id.'" class="slider-amount" maxlength="3" />';
  $slider .=   '</div>';
  $slider .= '</div>';
  return $slider;
}
?>


<fieldset class="nimrod-fld">
  <legend><?php echo __("Feature mixer") ?></legend>
  
  <p><?php echo __("Choose the importance you'd wish to assign to each feature and click on the <tt>Rearrange gettext messages</tt> button at the bottom of this page.") ?></p>
  
  <?php echo n_create_slider(NimrodPOUtil::KEY_COM_TR,  __("Translator comments"))    // Special Nimrod comments for a msgid
           , n_create_slider(NimrodPOUtil::KEY_COM_XT,  __("Developer comments"))     // Standard 'extracted comments' for that msgid
           , n_create_slider(NimrodPOUtil::KEY_REF,     __("Number of references"))   // Number of source files that use that msgid
           , n_create_slider(NimrodPOUtil::KEY_FREQ,    __("String frequency"))       // Number of times the msgid appeared; counting also across pages
           , n_create_slider(NimrodPOUtil::KEY_EL_NUM,  __("Element frequency"))      // Number of elements having the same msgid, on a per-page basis
           , n_create_slider(NimrodPOUtil::KEY_EL_VIS,  __("String visibility"))      // After page rendering
           , n_create_slider(NimrodPOUtil::KEY_EL_SIZE, __("Element size"))           // Bounding box size
           , n_create_slider(NimrodPOUtil::KEY_EL_INTERACT, __("Element interactions")) // TODO
           , n_create_slider(NimrodPOUtil::KEY_EL_CONTRAST, __("Element contrast"))     // TODO
           , n_create_slider(NimrodPOUtil::KEY_EL_SEMANTIC, __("Element semantics"))    // TODO
           ;
  ?>
  
  <br class="clear" />

  <script>
  jQuery(function(){
    var $ = jQuery.noConflict();
    
    $('.slider-bar').slider({
      orientation: "vertical",
      min: -100,
      max:  100,
      step: 5,
      slide: function(event, ui) {
        var inputId = '#' + $(this).data('input-id');
        $(inputId).val(ui.value);
      }, 
      change: function(event, ui) {
        // Remember settings in a cookie, since they are not very important
        var feat = $(this).data('feat');
        saveFeatWeight(feat, ui.value);
      }
    }).each(function(i, elem) {
      var f = $(elem).data('feat');
      var v = parseInt( $.cookie('nimrod-feat-' + f) ) || 0;
      var sliderId = '#' + $(elem).attr('id');
      var inputId  = '#' + $(elem).data('input-id');
      $(sliderId).slider('value', v);
      $(inputId).val(v);
    });

    $('input.slider-amount').keypress(function(e) {
      if (e.which == 13) {
        e.preventDefault();
        updateFeat(this);
      }
    }).change(function() {
      updateFeat(this);
    });

    function updateFeat(elem) {
      var sliderId = '#' + $(elem).data('slider-id');
      var value = $(elem).val();
      var feat  = $(elem).data('feat');
      $(sliderId).slider( 'value', value );
      saveFeatWeight(feat, value);
    }
    
    function saveFeatWeight(suffix, val) {
      $.cookie('nimrod-feat-' + suffix, val);
    }
    
  });
  </script>
      
</fieldset>
