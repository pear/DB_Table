<?php

require_once 'HTML/QuickForm.php';

/**
* US-English messages for some QuickForm rules.  Moritz Heidkamp
* suggested this approach for easier i18n.
*/
if (! isset($GLOBALS['_DB_TABLE']['qf_rules'])) {
	$GLOBALS['_DB_TABLE']['qf_rules'] = array(
	  'required'  => 'This element is required.',
	  'numeric'   => 'This element must be numbers only.',
	  'maxlength' => 'This element can be no longer than %d characters.'
	);
}


/**
* 
* DB_Table_QuickForm creates HTML_QuickForm objects from DB_Table properties.
* 
* DB_Table_QuickForm provides HTML form creation facilities based on
* DB_Table column definitions transformed into HTML_QuickForm elements.
* 
* $Id$
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
*
* @package DB_Table
*
*/

class DB_Table_QuickForm {
	
	/**
	* 
	* Build a form based on DB_Table column definitions.
	* 
	* @static
	* 
	* @access public
	* 
	* @param array $cols A sequential array of DB_Table column definitions
	* from which to create form elements.
	* 
	* @param string $array_name By default, the form will use the names
	* of the columns as the names of the form elements.  If you pass
	* $array_name, the column names will become keys in an array named
	* for this parameter.
	* 
	* @param array $args An associative array of optional arguments to
	* pass to the QuickForm object.  The keys are...
	*
	* 'formName' : String, name of the form; defaults to the name of the
	* table.
	* 
	* 'method' : String, form method; defaults to 'post'.
	* 
	* 'action' : String, form action; defaults to
	* $_SERVER['REQUEST_URI'].
	* 
	* 'target' : String, form target target; defaults to '_self'
	* 
	* 'attributes' : Associative array, extra attributes for <form>
	* tag; the key is the attribute name and the value is attribute
	* value.
	* 
	* 'trackSubmit' : Boolean, whether to track if the form was
	* submitted by adding a special hidden field
	* 
	* @return object HTML_QuickForm
	* 
	* @see HTML_QuickForm
	* 
	*/
	
	function &getForm($cols, $array_name = null, $args = array())
	{
		$formName = isset($args['formName'])
			? $args['formName'] : $this->table;
			
		$method = isset($args['method'])
			? $args['method'] : 'post';
		
		$action = isset($args['action'])
			? $args['action'] : $_SERVER['REQUEST_URI'];
		
		$target = isset($args['target'])
			? $args['target'] : '_self';
		
		$attributes = isset($args['attributes'])
			? $args['attributes'] : null;
		
		$trackSubmit = isset($args['trackSubmit'])
			? $args['trackSubmit'] : false;
		
		$form =& new HTML_QuickForm($formName, $method, $action, $target, 
			$attributes, $trackSubmit);
			
		DB_Table_QuickForm::addElements($form, $cols, $array_name);
		DB_Table_QuickForm::addRules($form, $cols, $array_name);
		
		return $form;
	}
	
	
	/**
	* 
	* Adds DB_Table columns to a pre-existing HTML_QuickForm object.
	* 
	* @static
	* 
	* @access public
	* 
	* @param object &$form An HTML_QuickForm object.
	* 
	* @param array $cols A sequential array of DB_Table column definitions
	* from which to create form elements.
	* 
	* @param string $array_name By default, the form will use the names
	* of the columns as the names of the form elements.  If you pass
	* $array_name, the column names will become keys in an array named
	* for this parameter.
	* 
	* @return void
	* 
	*/
	
	function addElements(&$form, $cols, $array_name = null)
	{
		foreach ($cols as $name => $col) {
			
			if ($array_name) {
				$elemname = $array_name . "[$name]";
			} else {
				$elemname = $name;
			}
			
			DB_Table_QuickForm::fixColDef($col, $elemname);

			$tmp =& DB_Table_QuickForm::getElement($col, $elemname);
			
			if (is_array($tmp)) {
				$form->addGroup($tmp, $elemname, $col['qf_label']);
			}
			
			if (is_object($tmp)) {
				$form->addElement($tmp);
			}
		}
	}
	
	
	/**
	* 
	* Build a single QuickForm element based on a DB_Table column.
	* 
	* @static
	* 
	* @access public
	* 
	* @param array $col A DB_Table column definition.
	* 
	* @param string $elemname The name to use for the generated QuickForm
	* element.
	* 
	* @return object HTML_QuickForm_Element
	* 
	*/
	
	function &getElement($col, $elemname)
	{
		if (isset($col['qf_setvalue'])) {
			$setval = $col['qf_setvalue'];
		}
		
		switch ($col['qf_type']) {
		
		case 'advcheckbox':
		case 'checkbox':
			
			$element =& HTML_QuickForm::createElement(
				$col['qf_type'],
				null,
				$col['qf_label'],
				null,
				$col['qf_attrs'],
				$col['qf_vals']
			);
			
			// WARNING: advcheckbox elements in HTML_QuickForm v3.2.2
			// and earlier do not honor setChecked(); they will always
			// be un-checked, unless a POST value sets them.
			if (isset($setval) && $setval == true) {
				$element->setChecked(true);
			} else {
				$element->setChecked(false);
			}
			
			break;
			
		case 'date':
		
			$col['qf_opts']['format'] = 'Y-m-d';
			
			$element =& HTML_QuickForm::createElement(
				'date',
				$elemname,
				$col['qf_label'],
				$col['qf_opts'],
				$col['qf_attrs']
			);
			
			if (isset($setval)) {
				$element->setValue($setval);
			}
			
			break;
			
		case 'time':
		
			$col['qf_opts']['format'] = 'H:i:s';
			
			$element =& HTML_QuickForm::createElement(
				'date',
				$elemname,
				$col['qf_label'],
				$col['qf_opts'],
				$col['qf_attrs']
			);
			
			if (isset($setval)) {
				$element->setValue($setval);
			}
			
			break;

		case 'timestamp':
		
			$col['qf_opts']['format'] = 'Y-m-d H:i:s';
			
			$element =& HTML_QuickForm::createElement(
				'date',
				$elemname,
				$col['qf_label'],
				$col['qf_opts'],
				$col['qf_attrs']
			);
			
			if (isset($setval)) {
				$element->setValue($setval);
			}
			
			break;
		
		case 'hidden':
		
			$element =& HTML_QuickForm::createElement(
				$col['qf_type'],
				$elemname,
				$col['qf_attrs']
			);
			
			if (isset($setval)) {
				$element->setValue($setval);
			}
			
			break;
			
			
		case 'radio':
		
			$element = array();
			
			foreach ($col['qf_vals'] as $btnvalue => $btnlabel) {
				
				if (isset($setval) && $setval == $btnvalue) {
					$col['qf_attrs']['checked'] = 'checked';
				}
				
				$element[] =& HTML_QuickForm::createElement(
					$col['qf_type'],
					null,
					null,
					$btnlabel . '<br />',
					$btnvalue,
					$col['qf_attrs']
				);
			}
			
			break;
			
		case 'select':
		
			$element =& HTML_QuickForm::createElement(
				$col['qf_type'],
				$elemname,
				$col['qf_label'],
				$col['qf_vals'],
				$col['qf_attrs']
			);
			
			if (isset($setval)) {
				$element->setSelected($setval);
			}
			
			break;
			
		case 'password':
		case 'text':
		case 'textarea':
		
			if (! isset($col['qf_attrs']['maxlength']) &&
				isset($col['size'])) {
				$col['qf_attrs']['maxlength'] = $col['size'];
			}
			
			$element =& HTML_QuickForm::createElement(
				$col['qf_type'],
				$elemname,
				$col['qf_label'],
				$col['qf_attrs']
			);
			
			if (isset($setval)) {
				$element->setValue($setval);
			}
			
			break;
		
		case 'static':
			$element =& HTML_QuickForm::createElement(
				$col['qf_type'],
				null,
				$col['qf_label'],
				(isset($setval) ? $setval : '')
			);
			break;
			
		default:
			
			/**
			* @author Moritz Heidkamp <moritz.heidkamp@invision-team.de>
			*/
			
			// not a recognized type.  is it registered with QuickForm?
			if (HTML_QuickForm::isTypeRegistered($col['qf_type'])) {
				
				// yes, create it with some minimalist parameters
				$element =& HTML_QuickForm::createElement(
					$col['qf_type'],
					$elemname,
					$col['qf_label'],
					$col['qf_attrs']
				);
				
				// set its default value, if there is one
				if (isset($setval)) {
					$element->setValue($setval);
				}
				
			} else {
				// element type is not registered with QuickForm.
				$element = null;
			}
			
			break;
		}
		
		// done
		return $element;
	}
	
	
	/**
	* 
	* Build an array of form elements based from DB_Table columns.
	* 
	* @static
	* 
	* @access public
	* 
	* @param array $cols A sequential array of DB_Table column
	* definitions from which to create form elements.
	* 
	* @param string $array_name By default, the form will use the names
	* of the columns as the names of the form elements.  If you pass
	* $array_name, the column names will become keys in an array named
	* for this parameter.
	* 
	* @return array An array of HTML_QuickForm_Element objects.
	* 
	*/
	
	function &getGroup($cols, $array_name = null)
	{
		$group = array();
		
		foreach ($cols as $name => $col) {
			
			if ($array_name) {
				$elemname = $array_name . "[$name]";
			} else {
				$elemname = $name;
			}
			
			DB_Table_QuickForm::fixColDef($col, $elemname);
			
			$group[] =& DB_Table_QuickForm::getElement($col, $elemname);
		}
		
		return $group;
	}
	
	
	/**
	* 
	* Adds element rules to a pre-existing HTML_QuickForm object.
	* 
	* @static
	* 
	* @access public
	* 
	* @param object &$form An HTML_QuickForm object.
	* 
	* @param array $cols A sequential array of DB_Table column definitions
	* from which to create form elements.
	* 
	* @param string $array_name By default, the form will use the names
	* of the columns as the names of the form elements.  If you pass
	* $array_name, the column names will become keys in an array named
	* for this parameter.
	* 
	* @return void
	* 
	*/
	
	function addRules(&$form, $cols, $array_name = null)
	{
		foreach ($cols as $name => $col) {
			
			if ($array_name) {
				$elemname = $array_name . "[$name]";
			} else {
				$elemname = $name;
			}
			
			DB_Table_QuickForm::fixColDef($col, $elemname);
			
			foreach ($col['qf_rules'] as $type => $opts) {
				
				switch ($type) {
					
				case 'required':
				case 'email':
				case 'lettersonly':
				case 'alphanumeric':
				case 'numeric':
				case 'nopunctuation':
				case 'nonzero':
					// $opts is the error message
					$form->addRule($elemname, $opts, $type);
					break;
				
				case 'minlength':
				case 'maxlength':
				case 'regex':
					// $opts[0] is the message, $opts[1] is the size or regex
					$form->addRule($elemname, $opts[0], $type, $opts[1]);
					break;
				
				default:
					break;
				}
			}
		}
	}
	
	
	/**
	* 
	* "Fixes" a DB_Table column definition for QuickForm.
	* 
	* Makes it so that all the 'qf_*' key constants are populated
	* with appropriate default values; also checks the 'require'
	* value (if not set, defaults to false).
	* 
	* @static
	* 
	* @access public
	* 
	* @param array &$col A DB_Table column definition.
	* 
	* @param string $elemname The name for the target form element.
	* 
	* @return void
	* 
	*/
	
	function fixColDef(&$col, $elemname)
	{	
		// always have a "require" value, false if not set
		if (! isset($col['require'])) {
			$col['require'] = false;
		}
		
		// array of acceptable values, typically for
		// 'select' or 'radio'
		if (! isset($col['qf_vals'])) {
			$col['qf_vals'] = null;
		}
		
		// the element type; if not set,
		// assigns an element type based on the column type.
		// by default, the type is 'text' (unless there are
		// values, in which case the type is 'select')
		if (! isset($col['qf_type'])) {
		
			switch ($col['type']) {
			
			case 'boolean':
				$col['qf_type'] = 'select';
				if (! isset($col['qf_vals'])) {
					$col['qf_vals'] = array(0 => 'No', 1 => 'Yes');
				}
				break;
			
			case 'date':
				$col['qf_type'] = 'date';
				break;
				
			case 'time':
				$col['qf_type'] = 'time';
				break;
				
			case 'timestamp':
				$col['qf_type'] = 'timestamp';
				break;
				
			case 'clob':
				$col['qf_type'] = 'textarea';
				break;
				
			default:
				if (isset($col['qf_vals'])) {
					$col['qf_type'] = 'select';
				} else {
					$col['qf_type'] = 'text';
				}
				break;
			}
		}
		
		// label for the element; defaults to the element
		// name
		if (! isset($col['qf_label'])) {
			$col['qf_label'] = $elemname . ':';
		}
		
		// special options for the element, typically used
		// for 'date' element types
		if (! isset($col['qf_opts'])) {
			$col['qf_opts'] = array();
		}
		
		// array of additional HTML attributes for the element
		if (! isset($col['qf_attrs'])) {
			// setting to array() generates an error in HTML_Common
			$col['qf_attrs'] = null;
		}
		
		// array of QuickForm validation rules to apply
		if (! isset($col['qf_rules'])) {
			$col['qf_rules'] = array();
		}
		
		// if the element is hidden, then we're done
		// (adding rules to hidden elements is mostly useless)
		if ($col['qf_type'] == 'hidden') {
			return;
		}
		
		// the element is required and not hidden
		if (! isset($col['qf_rules']['required']) &&
			$col['require']) {
			
			$col['qf_rules']['required'] =
				$GLOBALS['_DB_TABLE']['qf_rules']['required'];
			
		}
		
		// the element must be a number
		if (! isset($col['qf_rules']['numeric']) && (
				$col['type'] == 'smallint' ||
				$col['type'] == 'integer' ||
				$col['type'] == 'bigint' ||
				$col['type'] == 'decimal'||
				$col['type'] == 'single' ||
				$col['type'] == 'double'
			) ) {
			
			if (! isset($col['qf_rules']['numeric'])) {
				$col['qf_rules']['numeric'] =
					$GLOBALS['_DB_TABLE']['qf_rules']['numeric'];
			}
		}
		
		// the element has a maximum length
		if (! isset($col['qf_rules']['maxlength']) &&
			isset($col['size'])) {
		
			$max = $col['size'];
			
			$msg = sprintf(
				$GLOBALS['_DB_TABLE']['qf_rules']['maxlength'], $max
			);
			
			$col['qf_rules']['maxlength'] = array($msg, $max);
		}
	}
}

?>