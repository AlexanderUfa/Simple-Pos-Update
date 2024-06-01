<?php
 
/*
Plugin Name: Simple Pos Update
Plugin URI: https://www.vinogradov-ufa.ru
License: GPL3

*/

final class SimplePosUpdate
{
     /*имя столбца и произвольного поля поста по которому сортировка и вывод*/
     /*нужно ставить default value = 0*/
     const posFieldName = 'pos';
    
     
     public function __construct() 
     {
         /*добавляем колонки ко всем типам постов. get_current_screen() работает только после хука current_screen*/
         add_action( 'current_screen', array( $this, 'add_columns' ) );
         
         //запрос на обновление позиции
         add_action( 'wp_ajax_posupdate', array( $this , 'vin_posupdate' ) );
         add_action( 'wp_ajax_nopriv_posupdate', array( $this , 'vin_posupdate' )  );
         
     }
     
     
     public function add_columns()
     {
         $screen = (array) get_current_screen();
         $post_type = $screen['post_type'];
         $base = $screen['base'];
         
         //html::pre($screen);

         if ( $base == 'edit' ) 
         {
              // создаем новую колонку
             add_filter( 'manage_' . $post_type . '_posts_columns', array( $this, 'add_views_column' ) , 4 );
         
            // заполняем колонку данными
            add_action( 'manage_' . $post_type . '_posts_custom_column', array( $this, 'fill_views_column' ), 5, 2 );
         
            // добавляем возможность сортировать колонку
             add_filter( 'manage_' . 'edit-'. $post_type . '_sortable_columns', array( $this, 'add_views_sortable_column' ));
             
             //подключаем скрипты и стили
             add_action( 'admin_enqueue_scripts', array( $this , 'true_include_myscript') , 25 );
            // изменяем запрос при сортировке колонки
             add_filter( 'request', array( $this , 'add_column_views_request' )   );
             
             
         }
         
         
         
        
         
         
         
     }
     
     
     
     // создаем новую колонку
     public function add_views_column( $columns )
        {
	 

	$new_columns = [
		SimplePosUpdate::posFieldName  => 'Позиция',
	];

	return $columns + $new_columns  ;
        }
 
        
      // заполняем колонку данными  
     public function fill_views_column( $colname, $post_id ) 
     {
	if( $colname === SimplePosUpdate::posFieldName )
        { 
                $pos = get_post_meta( $post_id, SimplePosUpdate::posFieldName , true );
                /*if ( $pos!='' ) 
                {*/
                    //echo $pos;
                    //echo '<span class='vpos_ajax' data-postid=' . $post_id . >' . $pos . '</span>';
                     echo "<input type='text' class='vpos_ajax' name='position' data-postid=".$post_id." value='".$pos."' />";
                /*}*/
     
	}
     }   
     
     

        // добавляем возможность сортировать колонку
        public function add_views_sortable_column($sortable_columns) {
        $sortable_columns[SimplePosUpdate::posFieldName] = SimplePosUpdate::posFieldName;
        // false = asc (по умолчанию)
        // true  = desc

            return $sortable_columns;
         }
         


        //подключаем скрипты и стили
        public function true_include_myscript() 
        {
            
	wp_enqueue_script( 'posscript_js', SimplePosUpdate::getUrlPath() . '/posscript.js', array(), '3.0' );
        wp_enqueue_style( 'posscript_css', SimplePosUpdate::getUrlPath()  . '/posscript.css' );
        
        }
        
         // изменяем запрос при сортировке колонки
        public function add_column_views_request( $vars ) 
        {
	if( isset( $vars['orderby'] ) && $vars['orderby'] === SimplePosUpdate::posFieldName ){
		$vars['meta_key'] = SimplePosUpdate::posFieldName;
		$vars['orderby'] = 'meta_value_num';
	}

	return $vars;
        }
        
        

        public function vin_posupdate() : never
        {
        $position = intval( $_POST['position'] );
        $postid =  intval( $_POST['postid'] );
    
        update_post_meta( $postid, SimplePosUpdate::posFieldName , $position );
        echo $position;
        die();
        } 



         
         
    public static function getUrlPath() : string
     {
         return plugins_url( basename( __FILE__ , '.php' ) );
     }
     
     
     public static function getDirPath() : string
     {
         return plugin_dir_path( __FILE__ );
     }
     
 
}

$SimplePosUpdate = new SimplePosUpdate();

 


 
