<?php

/**
 * login_lang.php 
 *
 * Plugin to add a language selector in login screen
 *
 * @version 1.0
 * @author Hassansin
 * @https://github.com/hassansin
 * @example: http://kawaii.com
 */
class login_lang extends rcube_plugin
{
  
  public $task = 'login';  
  public $noajax = true;  
  public $noframe = true;

  function init()
  {    

    $this->add_hook('template_object_loginform', array($this, 'add_login_lang'));    //program/include/rcmail_output_html.php
    $this->add_hook('login_after',array($this,'change_lang'));
  }

  public function change_lang ($attr){        
    $user_lang = rcube::get_user_language();
    $lang = isset($_POST['_language'])? rcube_utils::get_input_value('_language', rcube_utils::INPUT_POST) : ($user_lang? $user_lang : rcube::get_instance()->config->get('language'));          
    rcube::get_instance()->load_language($lang);      
    $db = rcube::get_instance()->get_dbh();
    $db->query(
    "UPDATE ".$db->table_name('users').
    " SET language = ?".
    " WHERE user_id = ?",    
    $lang,
    $_SESSION['user_id']);
    return $attr;
  }

  public function add_login_lang($arg)
  {    
    $rcmail = rcube::get_instance();
    $this->load_config();

    $list_lang = $rcmail->list_languages();
    $user_lang = rcube::get_user_language();
    $current = $user_lang? $user_lang : $rcmail->config->get('language');          

    $label = $rcmail->gettext('language');          
    $label = $rcmail->config->get('language_dropdown_label')? $rcmail->config->get('language_dropdown_label'):$label;
    if(!$current)
      $current = 'en_US';

    $select = new html_select(array('id'=>"_language",'name'=>'_language','style'=>'width:100%;padding:3px;'));
    $select->add(array_values($list_lang),array_keys($list_lang));        

    
    $str  ='<tr>';
    $str .='<td class="title"><label for="_language">'.$label.'</label></td>';
    $str .='<td class="input">';
    $str .= $select->show($current);        
    $str .= '</td></tr>';

    if(preg_match('/<\/tbody>/', $arg['content'])){
      $arg['content'] = preg_replace('/<\/tbody>/', $str.'</tbody>', $arg['content']);
    }
    else{
      $arg['content'] = $arg['content'].$str;
    }

    // use exitings id's message and bottomline    
    //$rcmail->output->add_footer( $str );        
    return $arg;
  }
}

?>