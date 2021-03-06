<?php
/**
 * Create a confirmation
 *
 * @author Gerd Riesselmann
 * @ingroup Confirmations
 */
 
class CreateConfirmationsCommand extends CommandChain {
	/**
	 * Execute this command
	 */
	protected function do_execute() {
		$ret = new Status();

		$confirmation = new DAOConfirmations();
		$params = $this->get_params();
		$params['code'] = Common::create_token(); // Tokens must not be unique!
		$params['expirationdate'] = Arr::get_item($params, 'expirationdate', time() + GyroDate::ONE_DAY); // 24 hours default expiration
		
		Load::commands('generics/create');
		$cmd = new CreateCommand('confirmations', $params);
		$ret->merge($cmd->execute());
		$this->set_result($cmd->get_result());
		
		if ($ret->is_ok()) {
			$confirmation = $this->get_result();
			$handler = $confirmation->create_handler();
			$ret->merge($handler->created());
		}
		
		//$this->set_result($confirmation);
		return $ret;
	}	
} 
