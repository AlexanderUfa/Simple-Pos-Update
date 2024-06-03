<?php
 
/*
Plugin Name: Simple Pos Update
Description: Простой WordPress плагин для сортировки постов. Добавляет столбец "Позиция" с возможностью обновить позицию не выходя из списка постов.
Author URI: https://www.vinogradov-ufa.ru
Plugin URI: https://github.com/AlexanderUfa/Simple-Pos-Update.git
Author: Vinogradov
Requires PHP: 5.1
License: GPL3

*/

final class SimplePosUpdate
{
     /* имя столбца и произвольного поля поста по которому сортировка и вывод */
     /* name of the column and custom field of the post by which sorting and output */
     const posFieldName = 'pos';
    
     
     public function __construct() 
     {
          
         /* запускаем плагин только в админке */
         /* run the plugin only in the admin panel */
         if ( is_admin() ) 
         {
            /*добавляем колонки ко всем типам постов. get_current_screen() работает только после хука current_screen*/
            /*add columns to all post types. get_current_screen() only works after the current_screen hook*/
            add_action( 'current_screen', array( $this, 'add_columns' ) );
         
            //ajax запрос на обновление позиции
            //ajax request to update position
            add_action( 'wp_ajax_posupdate', array( $this , 'vin_posupdate' ) );
            add_action( 'wp_ajax_nopriv_posupdate', array( $this , 'vin_posupdate' )  );
             
         }

         
     }
     
     
     public function add_columns()
     {
         $screen = (array) get_current_screen();
         $post_type = $screen['post_type'];
         $base = $screen['base'];
         

         if ( $base == 'edit' ) 
         {
            // создаем новую колонку
            // create a new column
            add_filter( 'manage_' . $post_type . '_posts_columns', array( $this, 'add_views_column' ) , 4 );
         
            // заполняем колонку данными
            //fill the column with data
            add_action( 'manage_' . $post_type . '_posts_custom_column', array( $this, 'fill_views_column' ), 5, 2 );
         
            // добавляем возможность сортировать колонку
            // add the ability to sort the column
            add_filter( 'manage_' . 'edit-'. $post_type . '_sortable_columns', array( $this, 'add_views_sortable_column' ));
             
            //подключаем скрипты и стили
            //connect scripts and styles
            add_action( 'admin_enqueue_scripts', array( $this , 'true_include_myscript') , 25 );
            
            // изменяем запрос при сортировке колонки
            // change the query when sorting the column
            add_filter( 'request', array( $this , 'add_column_views_request' )   );
             
             
         }
 
     }
     
     
     
     // создаем новую колонку
     // create a new column
     public function add_views_column( $columns )
        {
	
	$new_columns = array( SimplePosUpdate::posFieldName  => 'Позиция' );
      
	return $columns + $new_columns ;
        
        }
 
        
      // заполняем колонку данными   
      // fill the column with data  
     public function fill_views_column( $colname, $post_id ) 
     {
	if( $colname === SimplePosUpdate::posFieldName )
        { 
                $pos = get_post_meta( $post_id, SimplePosUpdate::posFieldName , true );
             
                echo "<input type='text' class='vpos_ajax' name='position' data-postid=".$post_id." value='".$pos."' />";
               
     
	}
     }   
     
     

        // добавляем возможность сортировать колонку
        // add the ability to sort the column
        public function add_views_sortable_column($sortable_columns) {
            
            $sortable_columns[ SimplePosUpdate::posFieldName ] = SimplePosUpdate::posFieldName;

            return $sortable_columns;
         }
         


        //подключаем скрипты и стили
        //connect scripts and styles
        public function true_include_myscript() 
        {
            
	wp_enqueue_script( 'posscript_js', SimplePosUpdate::getUrlPath() . '/posscript.js', array(), '3.0' );
        wp_enqueue_style( 'posscript_css', SimplePosUpdate::getUrlPath()  . '/posscript.css' );
        
        }
        
         // изменяем запрос при сортировке колонки
        // change the query when sorting the column
        public function add_column_views_request( $vars ) 
        {
	if( isset( $vars['orderby'] ) && $vars['orderby'] === SimplePosUpdate::posFieldName ){
		$vars['meta_key'] = SimplePosUpdate::posFieldName;
		$vars['orderby'] = 'meta_value_num';
	}

	return $vars;
        }
        
        
        //обработка ajax запроса на обновление позиции поста
        //processing an ajax request to update a post position
        
        public function vin_posupdate() 
        {
        $position = intval( $_POST['position'] );
        $postid =  intval( $_POST['postid'] );
    
        update_post_meta( $postid, SimplePosUpdate::posFieldName , $position );
        echo $position;
        die();
        } 



         
     //возвращает url до папки плагина    
     //returns the url to the plugin folder
    public static function getUrlPath() 
     {
         return plugins_url( basename( __FILE__ , '.php' ) );
     }
     
     
     public static function getDirPath() 
     {
         return plugin_dir_path( __FILE__ );
     }
     
 
}

$SimplePosUpdate = new SimplePosUpdate();

 


 
