<?php

class wpMandrill_HowTos {
    static function show($section) {
        $section = strtolower($section);
        if ( !in_array($section, array('intro','auto','regular','filter', 'nl2br', 'direct','regions') ) ) $section = 'auto';
        
        $title = '';
        
        switch ($section) {
            case 'intro':
                break;
            case 'auto':
                $title = __('Mandrill: How to tell WordPress to use wpMandrill.', 'wpmandrill');
                break;
            case 'regular':
                $title = __('Mandrill: How to send a regular email.', 'wpmandrill');
                break;
            case 'filter':
                $title = __('Mandrill: How to modify a certain email using the <em>mandrill_payload</em> WordPress filter.', 'wpmandrill');
                break;
            case 'nl2br':
                $title = __('Mandrill: How to tell WordPress to change line feeds by BR tags in a certain email using the <em>mandrill_nl2br</em> WordPress filter.', 'wpmandrill');
                break;
            case 'direct':
                $title = __('Mandrill: How to send emails from within your plugins.', 'wpmandrill');
                break;
            case 'regions':
                $title = __('Mandrill: How to use multiple editable regions in your template.', 'wpmandrill');
                break;
        }
        
        $method = 'showSection' . ucwords($section);
        
        $html = self::$method();
        
        if ( $title != '' ) {
            $html = <<<HTML
            <div class="stuffbox" style="max-width: 90% !important;">
                <h3>$title</h3>
                <div style="width:90%; margin-left:auto;margin-right:auto;">
                    $html
                </div>
            </div>
HTML;
        }
        
        return $html;
    }
    
    static function showSectionIntro() {
			return  '<p>' . __('The purpose of this how-to is to show you how easy it is to start using the awesome platform that Mandrill offers to handle your transactional emails.', 'wpmandrill-how-tos').'</p>'
					. '<ol>'
					. '<li>'. __('Just by setting it up, all the emails sent from your WordPress installation will be sent using the power of Mandrill.', 'wpmandrill') . '</li>'
					. '<li>'. __('If you want further customization, you can use the <strong>mandrill_payload</strong> filter we\'ve provided.', 'wpmandrill') . '</li>'
					. '<li>'. __('And if you want an even greater integration between your application and Mandrill, we\'ve created a convenient call to send emails from within your plugins.', 'wpmandrill') . '</li>'
					. '</ol>'
					.'<p>' . __('You can learn more about all of these features right from this page.', 'wpmandrill').'</p>';
    }
    
    static function showSectionAuto() {
        
        return '
    <span class="setting-description">
        <p>'.__('Simply install wpMandrill and configure it to make it handle all the email functions of your WordPress installation.', 'wpmandrill').'</p>
        <p>'.__('Once it has been properly configured, it will replace the regular WordPress emailing processes, so it\'s basically transparent for you and for WordPress.', 'wpmandrill').'</p>
        <p>'.__('To test wpMandrill, log out, and try to use the <em>Forgot your password?</em> feature in WordPress (you don\'t need to reset your password though. Just check the headers of the email that it sends you, and you\'ll see that it comes from Mandrill\'s servers).', 'wpmandrill').'</p>
    </span>
        ';
    }

    static function showSectionRegular() {
        return '
    <span class="setting-description">
        <p>'.__('If you\'re a Plugin Developer, and you need to send a regular email using wpMandrill, you don\'t need to learn anything else. You can use the good ol\' <strong>wp_mail</strong> function, as you would normally do if you were not using this plugin.', 'wpmandrill').'</p>
        <p>'.__('For example:', 'wpmandrill').'</p>
        <p><blockquote><pre>'.__('&lt;?php wp_mail(\'your@address.com\', \'Your subject\', \'Your message\'); ?&gt;', 'wpmandrill').'</pre></blockquote></p>
    </span>
        ';
    }

    static function showSectionRegions() {
        return '
    <span class="setting-description">
        <p>'.__('By default, wpMandrill uses only one editable section of your templates: <em>main</em> so its use is transparent for the you.', 'wpmandrill').'</p>
        <p>'.__('If you need to use more than one, <em>main</em> included, you need to provided an array with the content to use for each editable region involved. This array should be supply in the field "html" of the payload.', 'wpmandrill').'</p>
        <p>'.__('This array has the same structure of the parameter <em>template_content</em> of the API call <a href="https://mandrillapp.com/api/docs/messages.html#method=send-template" target="_blank">send-template</a>:', 'wpmandrill').'</p>
        <p>'.__('$message["html"] = array( array("name" => region_name_1", "content" => "My awesome content for Region 1"), ... , array( "name" => region_name_2", "content" => "My awesome content for Region 2") )', 'wpmandrill').'</p>
        ';
    }
    
    static function showSectionFilter() {
        return '
    <span class="setting-description">
        <p>'.__('if you need to fine tune one or some of the emails sent through your WordPress installation, you will need to use the <em>mandrill_payload</em> filter.', 'wpmandrill').'</p>
        <p>'.__('To use it, you must create a function that analyzes the payload that is about to be sent to Mandrill, and modify it based on your requirements. Then you\'ll need to add this function as the callback of the mentioned filter, using the <em>add_filter</em> WordPress call. And finally, insert it into your theme\'s functions.php file or you own plugin\'s file.', 'wpmandrill').'</p>
        <p>'.__('You can use the following code as an skeleton for your own callbacks:', 'wpmandrill').'</p>
        <p>
            <blockquote><pre>
                &lt;?php
                &nbsp;&nbsp;&nbsp;function my_callback($message) {
	                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if ( my_condition($message) ) {
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$message = my_process($message)
	                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}
	                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return $message;
                &nbsp;&nbsp;&nbsp;}
                &nbsp;&nbsp;&nbsp;add_filter( \'mandrill_payload\', \'my_callback\' );
                ?&gt;
            </pre></blockquote>
        </p>
        <p>'.__('Let\'s say you\'re using the <a href="http://wordpress.org/extend/plugins/cart66-lite/" target="_blank">Cart66 Lite Ecommerce plugin</a> and you want to modify the emails sent from this plugin. Here\'s what you should do:', 'wpmandrill').'</p>
        <p>
            <blockquote><pre>
                &lt;?php
                &nbsp;&nbsp;&nbsp;function cart66Emails($message) {	                
	                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if ( in_array( \'wp_Cart66Common::mail\', $message[\'tags\'][\'automatic\'] ) ) {
		                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;// do anything funny here...
		                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if ( isset($message[\'template\'][\'content\'][0][\'content\']) )
			                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$html = &$message[\'template\'][\'content\'][0][\'content\'];
		                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;else
			                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$html = &$message[\'html\'];

		                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$html = nl2br($html);

	                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}
	                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return $message;
                &nbsp;&nbsp;&nbsp;}
                &nbsp;&nbsp;&nbsp;add_filter( \'mandrill_payload\', \'cart66Emails\' );
                ?&gt;
            </pre></blockquote>
        </p>
    </span>
        ';
    }
    
    static function showSectionNl2br() {
        return '
    <span class="setting-description">
        <p>'.__('That\'s easy! Just do something like this:', 'wpmandrill').'</p>
        <p>
            <blockquote><pre>
                &lt;?php
                &nbsp;&nbsp;&nbsp;function forgotMyPasswordEmails($nl2br, $message) {	                
	                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if ( in_array( \'wp-retrieve_password\', $message[\'tags\'][\'automatic\'] ) ) {
		                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$nl2br = true;
	                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}
	                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return $nl2br;
                &nbsp;&nbsp;&nbsp;}
                &nbsp;&nbsp;&nbsp;add_filter( \'mandrill_nl2br\', \'forgotMyPasswordEmails\' );
                ?&gt;
            </pre></blockquote>
        </p>
    </span>
        ';
    }
    static function showSectionDirect() {
        return '
    <span class="setting-description">
        <p>'.__('If you are a Plugin Developer and you need to create a deep integration between Mandrill and your WordPress installation, wpMandrill will make your life easier.', 'wpmandrill').'</p>
        <p>'.__('We have exposed a simple function that allows you to add tags and specify the template to use, in addition to specifying the To, Subject and Body sections of the email:','wpmandrill').'</p>
        <p><blockquote><pre>'.__('&lt;?php wpMandrill::mail($to, $subject, $html, $headers = \'\', $attachments = array(), $tags = array(), $from_name = \'\', $from_email = \'\', $template_name = \'\'); ?&gt;', 'wpmandrill').'</pre></blockquote></p>
        <p>'.__('But if you need Mandrill Powers, we have included a full-featured PHP class called Mandrill. It has every API call defined in Mandrill\'s API. Check it out at <em>/wp-content/plugin/wpmandrill/lib/mandrill.class.php</em>.', 'wpmandrill').'</p>
        <p>'.__('To use it, just instantiate an object passing your API key, and make the calls:', 'wpmandrill').'</p>
        <p><blockquote><pre>'.__('&lt;?php $mandrill = Mandrill($my_api_key); echo $mandrill->ping(); ?&gt;', 'wpmandrill').'</pre></blockquote></p>
    </span>
        ';
    }

}

?>
