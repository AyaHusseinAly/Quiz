<?php

class Exam implements Exam_interface {

    private $url;
    private $questions;
    private $auto_evaluated_questions;
    private $user_answers=array();
    private $question_number;
   
    
    public function __construct() {
        session_start();
        $this->url = Helper::get_current_Page_URL();
        $this->questions = $this->get_questions();
    
    }

    public function getNumberOfQuestions() {
        return count($this->questions);
    }

    public function getNumberOf_autoEvaluatedQuestions() {
        return count($this->auto_evaluated_questions);
    }

    public function getUserAnswers() {
        return ($this->user_answers);
    }
    public function get_questionsArr() {
        return ($this->questions);
    }
    public function get_autoEvaluated_questionsArr() {
        return ($this->auto_evaluated_questions);
    }

/************************************* Navigation between Pages*******************************************/

    public function move_previous() {
      // $_SESSION['page']--; //Not working here :)
       if(strpos($this->url,"?")!==false){
            $url_parts = explode("?", $this->url);
            return ($url_parts[0])."?dec=true";
       }
       else{
           if($_SESSION['page']!=0){
            return ($this->url)."?dec=true";
           }
       }
    }

    public function move_next() {
        //$_SESSION['page']++; //Not working here :)
       if(strpos($this->url,"?")!==false){
            $url_parts = explode("?", $this->url);
            return ($url_parts[0])."?inc=true";
       }
       else{
            return ($this->url)."?inc=true";
       }

    }

    public function getPage(){

        if(!array_key_exists('page', $_SESSION)){
            $_SESSION['page']=0;
        }
        else{
            if (isset($_GET['inc'])==true) { 
                $_SESSION['page']++;
            }
    
            if (isset($_GET['dec'])==true) { 
                $_SESSION['page']--;
            }
        }  
        return $_SESSION['page'];

    }

    public function load_exam_page($page) {
        if (isset($this->questions[$page])) {
            return $this->questions[$page];
        } else {

            throw new Exception("Question doesn't exist ");
        }
    }
/******************************** Read Exam Questions from file *************************************/
   private function get_questions() {
        $lines = file(exam_file);
        $questions = array();
        foreach ($lines as $line) {
            if (substr($line, 0, 1) === "Q") {
                if (isset($new_mcquestion)) {
                    $questions[] = $new_mcquestion;
                    $this->auto_evaluated_questions[]=$new_mcquestion;
                }
                $new_mcquestion = new MCQuestion($line);
            } elseif (substr($line, 0, 2) === "*Q") {
                $new_tofquestion = new TrueOrFalseQuestion(str_replace("*", "", $line));
                $questions[] = $new_tofquestion;
                $this->auto_evaluated_questions[]=$new_tofquestion;
            } elseif (substr($line, 0, 1) === "#") {
                $new_essayquestion = new EssayQuestion(str_replace("#", "", $line));
                $questions[] = $new_essayquestion;    
            } elseif (substr($line, 0, 3) === "Ans") {
                $new_mcquestion->Add_Answer(substr($line, 5, 7));
            } elseif (substr($line, 0, 4) === "*Ans") {
                $new_tofquestion->Add_Answer(substr($line, 6, 7));             
            } else {
                $new_mcquestion->Add_an_Option($line);
            }
        }
        return $questions;
    }

/****************************** For View Results Feature **********************************/    
    public function store_answer(){
       
        if(isset($_POST['Q'])){
            $_SESSION['answers']['Q'.$_SESSION['page']]=$_POST['Q'];
        }

        if(isset($_SESSION['answers'])){
            $this->user_answers=$_SESSION['answers'];
            echo "<pre style='color:green'>";print_r($_POST);echo "</pre>";
            echo "<pre style='color:green'>";print_r($_SESSION['answers']);echo "</pre>";
        }
  
       
    }
    
    public function mark_exam(){
        $mark=0;
        $i=0;
        foreach($this->auto_evaluated_questions as $q){
            if(array_key_exists('Q'.($i+1), $this->user_answers)){
                if(($q->get_answer())==$this->user_answers['Q'.($i+1)]){
                    $mark++;    
                }
            }
            $i++;
        }
        return $mark;
        
    }

}
