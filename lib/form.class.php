<? 
/**
 * Form.php
 *
 * The Form class is meant to simplify the task of keeping
 * track of errors in user submitted forms and the form
 * field values that were entered correctly.
 *
 * Author : Srinath
 */
 
class Form
{
   var $values = array();  //Holds submitted form field values
   var $errors = array();  //Holds submitted form error messages
   var $numErrors;   //The number of errors in submitted form

   /* Class constructor */
   function Form(){
      /**
       * Get form value and error arrays, used when there
       * is an error with a user-submitted form.
       */
      if(isset($_SESSION['valueArray']) && isset($_SESSION['errorArray'])){
         $this->values = $_SESSION['valueArray'];
         $this->errors = $_SESSION['errorArray'];
         $this->numErrors = count($this->errors);

         unset($_SESSION['valueArray']);
         unset($_SESSION['errorArray']);
      }
      else{
         $this->numErrors = 0;
      }
   }

   /**
    * setValue - Records the value typed into the given
    * form field by the user.
    */
   function setValue($field, $value){
      $this->values[$field] = $value;
   }

   /**
    * setError - Records new form error given the form
    * field name and the error message attached to it.
    */
   function setError($field, $errmsg){
      $this->errors[$field] = $errmsg;
      $this->numErrors = count($this->errors);
   }

   /**
    * value - Returns the value attached to the given
    * field, if none exists, the empty string is returned.
    */
   function value($field) {
      if(array_key_exists($field,$this->values)) {
         return htmlspecialchars(stripslashes($this->values[$field]));
      }else{
         return "";
      }
   }

   /**
    * error - Returns the error message attached to the
    * given field, if none exists, the empty string is returned.
    */
   function error($field){
      if(array_key_exists($field,$this->errors)){
         return $this->errors[$field];
      }else{
         return "";
      }
   }

   /* getErrorArray - Returns the array of error messages */
   function getErrorArray(){
      return $this->errors;
   }
};

$form = new Form();
?>
