<?php

    /* 
     * *******************************************************************
     * Класс:  SimplePages
     *
     * Описание: Предназанечен для формирования пагинаторов 
     *            
     * Методы:
     *           
     *      SimplePages          - конструктор
     *      setPagesContent      - установка отображения блоков пагенатора
     *      getPages             - получение массива  хэшей для формирования пагинатора
     *
     *     
     * ****************************************************************
     *
     * Пример соответсвующего кода шаблона для формимрования пагинатора:
     *
     *  <TMPL_LOOP pages>
     *  <TMPL_IF NAME="__first__">            
     *  <TMPL_IF prev_page><a href="<TMPL_VAR SNAME>?action=show_events_list&page=<TMPL_VAR page>&event_type=<TMPL_VAR event_type>" class="options">Prev</a></TMPL_IF>
     *  </TMPL_IF>
     *  <TMPL_UNLESS current_page>
     *  <a href="<TMPL_VAR SNAME>?action=show_events_list&page=<TMPL_VAR page>&event_type=<TMPL_VAR event_type>" class="options"><TMPL_VAR content_page></a>
     *  <TMPL_ELSE>
     *  <strong><TMPL_VAR content_page></strong>
     *  </TMPL_UNLESS>
     *  <TMPL_UNLESS NAME="__last__">::
     *  <TMPL_ELSE>
     *  <TMPL_IF next_page><a href="<TMPL_VAR SNAME>?action=show_events_list&page=<TMPL_VAR page>&event_type=<TMPL_VAR event_type>" class="options">Next</a></TMPL_IF>
     *  </TMPL_UNLESS>
     *  </TMPL_LOOP>
     *  
     * ****************************************************************
    */


    /* 
     * *******************************************************************
     * Установки отображения 
     * *****************************************************************
    */         

	define("PAGE_NUMBER",0);    //для показа номера страницы
    define("RECORDS_RANGE",1);  //для показа интервала записей, которые попадают на страницу

    class SimplePages extends BaseObject
    {
        var $m_total_count;
        var $m_current_page;
        var $m_records_on_page;
        var $m_content_page;
        var $m_pages_count_on_page;
        
        
        function SimplePages($total_records=0,$current_page=0,$records_on_page=10,$pages_count_on_page=10)
        {
            $this->m_total_count         = $total_records;
            $this->m_current_page        = $current_page;
            $this->m_records_on_page     = $records_on_page;
            $this->m_page_content        = RECORDS_RANGE;
            $this->m_pages_count_on_page = $pages_count_on_page;
        }
        
        
        /* 
         * *******************************************************************
         * Функция:  setPageContent
         *
         * Описание: устанавливает каким образом будет отображаться 
         *           каждый пункт пагинатора
         *           
         * Параметары:
         *
         * $page_content - либо PAGE_NUMBER - [1] - [2] - [3] - [4], 
         *                 либо RECORDS_RANGE - [1-10] - [10-20] - [20-30]
         * *******************************************************************
        */                             
        function setPageContent($page_content)
        {
            if( $page_content == PAGE_NUMBER || $page_content == RECORDS_RANGE )
                $this->m_page_content = $page_content;
            else
                $this->m_page_content = PAGE_NUMBER;
        }
        
        
        /* 
         * *******************************************************************
         * Функция:  getPages
         *
         * Описание: формирует и возвращает массив хэшей для описания 
         *           пагинатора
         *           
         * Параметары:
         *
         * $total_records       - общее число элементов в списке
         * $current_page        - текущая страница
         * $records_on_page     - количество записей на одной странцие списка 
         * $pages_count_on_page - количество отображаемых страниц в пагенаторе
         *
         * *******************************************************************
        */                                     
        function getPages($total_records=0,$current_page=0,$records_on_page=10,$pages_count_on_page=10)
        {
            
            if( $total_records != 0 ){             
                $this->m_total_count         = $total_records;
                $this->m_current_page        = $current_page;
                $this->m_records_on_page     = $records_on_page;    
                $this->m_pages_count_on_page = $pages_count_on_page;
            }
                        
            if( $this->m_records_on_page <= 0 )
                return false;

            
            $pages_count = intval($this->m_total_count / $this->m_records_on_page);
            if( $pages_count * $this->m_records_on_page < $this->m_total_count ){
                $pages_count++;
            }


            $pages = array();


            if( $current_page > 0 && $pages_count > 1){
                $page = array();
                
                $page["page"]         = $current_page - 1;                
                $page["prev_page"]    = 1;                

                $pages[] = $page;
            }
            
            $start_page = 0;            
            $end_page   = $pages_count;
            
            if( $pages_count > $this->m_pages_count_on_page ){                
                $start_page = $this->m_current_page - 8;                
                if( $start_page < 0 ){
                    $start_page = 0;
                }                    
                
                $end_page   = $start_page + $this->m_pages_count_on_page;
                if( $end_page > $pages_count ){
                    $end_page = $pages_count;
                }
            }
                                                    
            for( $i = $start_page; $i < $end_page; $i++ ){
                $page = array();
                
                //$content;
                switch( $this->m_page_content ){
                    
                    case PAGE_NUMBER:
                        $content = $i+1;
                        break;
                        
                    case RECORDS_RANGE:
                    
                        $begin_content = $i * $this->m_records_on_page;
                        $end_content   = $begin_content + $this->m_records_on_page;
                        
                        if( $begin_content == 0){
                            $begin_content = 1;
                        }
                             
                        if( $end_content > $this->m_total_count ){
                            $end_content = $this->m_total_count;
                        }
                    
                        $content = $begin_content." - ".$end_content;
                        break;
                }
                
                                
                $page["content_page"] = $content;
                $page["page"]         = $i;
                
                if( $current_page == $i ){
                    $page["current_page"] = 1;
                }

                $pages[] = $page;
            } 
            
            if(  ( $current_page < ($pages_count-1)) && count($pages) > 0 ){
                $page = array();
                
                $page["page"]         = $current_page+1;
                $page["next_page"]    = 1;
                $page["max_page"]     = $this->m_total_count-1;
                
                $pages[] = $page;
            }

            if(count($pages) == 1) return array();
            else return $pages;
        }
    }

?>