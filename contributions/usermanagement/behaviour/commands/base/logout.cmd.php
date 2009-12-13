<?php
/**
 * @author Gerd Riesselmann
 */
class LogoutUsersBaseCommand extends CommandComposite {
	/**
	 * Returns title of command.
	 */
	public function get_name() {
		return 'logout';
	}
	
	public function execute() {
		$ret = parent::execute();
		if ($ret->is_ok()) {
			Load::commands('generics/triggerevent');
			$cmd = new TriggerEventCommand('logout', false);
			$cmd->execute();			
		}
	}
	
	protected function do_execute() {
		$ret = new Status();
		
		Session::clear();
		$cmd = CommandsFactory::create_command('permanentlogins', 'end', false);
		$this->append($cmd);
		
		return $ret;
	}
}
