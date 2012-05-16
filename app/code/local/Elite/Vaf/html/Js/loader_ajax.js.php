var getUrl = function( form, requestLevel ) {
    var requestLevel = requestLevel.replace(' ','_');
    var select = form.find('.' + requestLevel + 'Select');
    var value = select.val();
    
    var product = jQuery('#vafProduct').val();
    var url = '<?=Mage::getBaseUrl( Mage_Core_Model_Store::URL_TYPE_LINK, isset($_SERVER['HTTPS']) )?>vaf/ajax/process?';
    
    var levels = select.metadata().prevLevelsIncluding.split(',');
    
    url = url + '&product=' + product;
    url = url + '&front=<?=$main->isFront() ? 1 : 0?>';
    url = url + '&requestLevel=' + requestLevel;
    
    $.each(levels, function(index,level){
        var level = level.replace(' ','_');
        if(level==requestLevel) {
            return;
        }
        url = url + '&' + level + '=' + form.find('.' + level + 'Select').val();
    });
    
    return url;
}

<?php
function isLastLevel( $i, $c ) {
    return $i == $c - 2;
}

/** Iterate each level ( except the "leaf" level ) and create a function to load it's children */
for( $i = 0; $i < $c - 1; $i++ )
{
    $level = $levels[ $i ];
    echo "\n";
    ?>
    
    var load<?=str_replace(' ','_',ucfirst( $levels[ $i + 1 ] ))?>s = function() {
        
        var callbackFunc = function(){
        
            showSelect(this);
            <?php
            if( isset( $levels[ $i + 2 ] ) )
            {
                ?>
                loadNextSelectIfOneOption( '<?=str_replace(' ','_',$levels[ $i + 1 ])?>', '<?=str_replace(' ','_',$levels[ $i + 2 ])?>' );
                <?php
            } 
            if( shouldAutoSubmit() && isLastLevel( $i, $c ) )
            {
                ?>
                <?//autoSubmit();?>
                <?php
            }
            ?>
            decorateUnavailableSelections();
            jQuery(this).trigger('vafLevelLoaded');
        };
        
        decorateUnavailableSelections();
                
        var url = getUrl( jQuery(this).parent('form'), '<?=str_replace(' ','_',$levels[ $i + 1 ])?>' );
        var loadingText = '<option value="0"><?=htmlentities( addslashes( $this->loadingText() ) )?></option>';
        
        jQuery(this).nextAll('.<?=str_replace(' ','_',$levels[ $i + 1 ])?>Select').html( loadingText );
        
        jQuery(this).nextAll('.<?=str_replace(' ','_',$levels[ $i + 1 ])?>Select').load( url, {}, function(responseText) {
            jQuery(this).html(responseText);
            callbackFunc.apply( this );
        });  
        <?php
        for( $j = $i + 2; $j < $c; $j++ )
        {
            ?>
            jQuery('.<?=str_replace(' ','_',$levels[ $j ])?>Select').html('<option></option>');
            <?php
        } 
        ?>
    }
    <?php
}