<?php
class CLI {

	private CLIParser $parser;
	private CLIOutput $output;

	public function __construct(CLIParser $parser) {
		$this->parser = $parser;
		$this->output = new CLIOutput();
	}

	public function checkTask(){
		$this->help();
	}

	private function help(){
		$this->output->print(array(
			'Moin' => 'Oiin'
		));
	}
}
?>