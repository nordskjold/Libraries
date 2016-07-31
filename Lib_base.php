<?php

	class Lib_base extends database {
		
		private $lib_errors;
		
		/**
		 * Disabling extending of database functions, restricting database to base only
		 */
		final protected function executeInsertStmt($table_name, $model, $assoc_model_array) {
			parent::executeInsertStmt($table_name, $model, $assoc_model_array);
		}
		
		final protected function executeInsertRelationStmt($table_name, $model) {
			parent::executeInsertRelationStmt($table_name, $model);
		}
		
		final protected function executeUpdateStmt($table_name, $model, $assoc_model_array) {
			parent::executeUpdateStmt($table_name, $model, $assoc_model_array);
		}
		
		final protected function find($id, $table_name, $table_id) {
			parent::find($id, $table_name, $table_id);
		}
		
		final protected function findAll($filter, $order, $limit, $table_name) {
			parent::findAll($filter, $order, $limit, $table_name);
		}
		
		/**
		 * Check for library errors.
		 * 
		 * @return boolean <b>TRUE</b> if theres no errors, <b>FALSE</b> if there is.
		 */
		public function isLibErrorsEmpty() {
			if(! empty($this->lib_errors)) {
				return false;
			} else {
				return true;
			}
		}
		
		/**
		 * Stores a library error message.
		 *
		 * @param string $message
		 * <p>The error message to store.</p>
		 * 
		 * @return boolean <b>FALSE</b> is always returned for saving of brackets in libraries.
		 */
		protected function addLibError($message = null) {
			if($message) {
				if(! is_array($this->lib_errors)) {
					$this->lib_errors = array();
				}
				
				$this->lib_errors[get_called_class()][] = $message;
			}

			return false;
		}

		/**
		 * Get library error messages.
		 * 
		 * @return string|boolean The error messages, <b>FALSE</b> if empty.
		 */
		public function getLibError() {
			if(! $this->isLibErrorsEmpty() && array_key_exists(get_called_class(), $this->lib_errors)) {
				$return = "";

				foreach($this->lib_errors[get_called_class()] as $error) {
					$return .= rtrim(ucfirst($error), '.'). '.<br />';
				}
				
				return rtrim($return, '<br />');
			} else {
				return false;
			}
		}
		
		/**
		 * Get the User ID
		 *
		 * @return int|boolean The User ID if logged in, <b>FALSE</b> if not.
		 */
		protected function getUserId() {
			if(isset($_SESSION['logged'])) {
				return (int)$_SESSION['logged'];
			} elseif(array_key_exists("remember_login", $_COOKIE)) {
				return $_COOKIE["remember_login"];
			} else {
				return false;
			}
		}
	}

?>