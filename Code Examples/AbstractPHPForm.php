<?php

abstract class MyForm {
    protected $errorMessage;
    protected $dataListName;
    protected $sessionFormNumber;

// Runs the form based on what is in functions printForm and processFrom
public function runForm() {
    
    $this->processForm();

    if($GLOBALS["currentForm"]) {
        $this->printForm();       
    }
}

// Build out in child classes
abstract function printForm();

// Build out in child classes
abstract function processForm();


/**
 * test the input data for unwanted characters and return the 
 * input with those characters removed.
 */
function testInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }

/**
 * Test that the value within the field is valid, and did not return an error.
 * If an error is returned make the class of the message text equel error. (Make the text red)
 */
  function isValid($error, $message = "*") {
    if(empty($error)) {
      return $message;
    } else {
      return "<span class=\"error\">".$message."</span>";
    }
}

/**
 * Sets the style for elements in the form.
 */
function setStyle() {
    ?>

        <style>
            	h1 {padding:15px; }
				h2 {padding-left:20px;}
                p {margin-left: 2em;}
                .main_form{padding-left:30px;}
                .main_table {text-align: left;}
				.main_table th, td { padding-right:20px; }
				.error {color: #FF0000;}
                .button{
                        color:rgb(255,255,255); 
                        background-color:rgb(0,122,201);
                    }
                .data_display {text-align: left;}
                .data_display th, td { padding-right:20px; }
                .data_display {overflow-x:auto;}
                input:invalid {
                    border: 2px solid red;
                }
        </style>

    <?php
}

/**
 * Sets the form headers.
 */
function setHeaders($formName, $message = "") { 
    ?>
        <h1><IMG SRC="/My_Logo.jpg" ALT="My Logo Text" WIDTH=440 HEIGHT=80></h1>
		<h2><?php echo $formName; ?></h2>
        <p><?php echo $message ?></p>
		<p><?php echo $this->isValid($this->errorMessage, "* required field"); ?></p>
        <p><span class="error"><?php echo $this->errorMessage; ?></span></p>


    <?php
}

/**
 * formats tables and buttons to be in desired position on page.
 */
function format() {
    ?>
        <script>
                var table = document.getElementsByClassName("main_table")[0];
                var addButton = document.getElementById("add");
                var nextButton = document.getElementById("nextButton");
                var displayTable = document.getElementById("display_table");

                addButton.style = "margin-left:" + (table.rows[0].cells[0].offsetWidth - 10) + "px";
                nextButton.style = "margin-left:" + (table.rows[0].cells[0].offsetWidth + table.rows[0].cells[1].offsetWidth + table.rows[0].cells[2].offsetWidth) + "px";
                displayTable.style = "margin-left:" + (table.rows[0].cells[0].offsetWidth) + "px";

        </script>

    <?php
}

  /**
   * Create a combo-box with given name.
   * Possible values are from the passed $list_data array.
   * Gives error if Item entered is not in the list.
   */
function comboBox($name, $list_data, $submitOnChange = false){
    if($submitOnChange) {
        $change = "this.form.submit()";
    } else {
        $change = "";
    }
    ?>
    <input list=<?php echo $name."combobox";?> value="<?php echo $this->setValue($name);?>" name=<?php echo $name;?> onchange= <?php echo $change ?> autocomplete="off" pattern= 
                            <?php  
                            $pattern_values=$list_data; 	//get array of values
                            $replaceValues = array(" ", "(", ")");
                            $newValues = array("\s", "\(", "\)");
                            $length = count($pattern_values);
                            $regex = "";
                            For($x=0; $x < ($length-1); $x++) { 	//loops through each value
                                $tempValue = str_replace($replaceValues, $newValues, $pattern_values[$x]);
                                $regex .= "(".$tempValue.")|";
                            }
                            $tempValue = str_replace($replaceValues, $newValues, $pattern_values[$length-1]);
                            $regex .= "(".$tempValue.")";
                            //$regex = str_replace(array(" ", "(", ")"), array("\s", "\(", "\)"), $regex);
                            echo $regex;
                            ?>  
                            title= "Must be a valid value">
                    <datalist id=<?php echo $name."combobox";?>>
                            <?php  
                            $combo_values=$list_data; 	//get array of values
                            Foreach($combo_values as $value) { 	//loops through each value
                            ?>
                        <option value="<?php echo $value; ?>"> <?php } ?> 
                </datalist>
                <noscript><input type="submit" value="Submit"></noscript>
        <?php
}

//create a drop-down with name given with options from provided array.
function dropDown($name = "", $list_data, $submitOnChange = false){

    if($submitOnChange) {
        $change = "this.form.submit()";
    } else {
        $change = "";
    }

    ?>
        <select name= <?php echo $name;?> onchange= <?php echo $change ?>>
            <?php  
                $values=$list_data; 	//get array of values
                Foreach($values as $value) { 	//loops through each value
            ?>
                <option value="<?php echo $value; ?>"<?php
                        if ($this->setValue($name) == $value) {
                            echo " selected";
                        }
                    ?>><?php echo $value; ?></option><?php } ?>
        </select>
        <noscript><input type="submit" value="Submit"></noscript>
    <?php
}

//use to set value of an item so it will not change on refresh.
function setValue($name){
    if(isset($_POST[$name]) && $GLOBALS['keepSet']){
        return $this->testInput($_POST[$name]);
    }else {
        return "";
    }
}

/**
 * Add the given object to the season list given by name.
 */
function addObjectToList($Object, $SessionListName) {
    $serializedTempObject = serialize($Object);
    $_SESSION[$SessionListName][] = $serializedTempObject;
}

/**
 * fomat data to be displayed by dataListDisplay
 */
protected function objectListDisplay($objectListName, $columnTitles) {

    array_unshift($columnTitles, "Remove");
    $objectDataList;

    $listLength = count($_SESSION[$objectListName]);
    for($x = 0; $x < $listLength; $x++) {
        //get array from current object to be displayed
        $object = unserialize($_SESSION[$objectListName][$x]);
        $tempArray = $object->getDisplayArray();
        //Add Remove button to front of $tempArray
        array_unshift($tempArray, "<button type=\"submit\" class=\"button\" name=\"".$objectListName."Button\" onclick=\" setRemoveRowNumber(".$x.")\">Remove</button>");

        $objectDataList[] = $tempArray;
        
    }

    $this->dataListDisplay($columnTitles, $objectDataList);
}

// $column_titles: a list of what each column from $data should be labeled ()
// $data: two dimensional array with data for each item in it's own row.
protected function dataListDisplay($column_titles, $data){
    ?>

    <table name="data_table" class="data_display" id="display_table">

            <tr>
                <?php
                Foreach($column_titles as $title) {
                    ?>
                        <th><?php echo $title; ?></th>
                    <?php
                }
                ?>
            </tr>

            <?php
                Foreach($data as $data_row) {
                    ?>
                    <tr>
                        <?php
                            Foreach($data_row as $item) {
                                ?>
                                    <td><?php echo $item; ?></td>
                                <?php
                            }
                        ?>
                        </tr>
                    <?php
                }
                ?>

                <tr>
					<td>&nbsp;</td>		<!--empty cell-->
				</tr>
                
    </table>

    <script>
        function setRemoveRowNumber(row_number) {
            document.getElementById('RemoveRowValue').value = row_number;
        }
    </script>

    <?php
    
}

// $list_name: the name of the list in $GLOBALS
// $row_number: the row you want deleted
protected function removeRowFromList($list_name, $row_number) {
        unset($_SESSION[$list_name][$row_number]);
        $_SESSION[$list_name] = array_values($_SESSION[$list_name]);
}

/**
 * Determines if the back button should be visible for a form.
 * Needed for modify requests.
 */
function getBackButtonType() {
    if(firstFormNumber() == $this->sessionFormNumber) {
        return "hidden";
    } else {
        return "submit";
    }
}

/**
 * Determines if the Next button should say Next or Submit.
 * Needed for modify requests.
 */
protected function getNextButtonValue() {
    if(lastFormNumber() == $this->sessionFormNumber) {
        return "Submit";
    } else {
        return "Next";
    }
}

}
?>