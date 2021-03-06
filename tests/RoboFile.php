<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * Download robo.phar from http://robo.li/robo.phar and type in the root of the repo: $ php robo.phar
 * Or do: $ composer update, and afterwards you will be able to execute robo like $ php vendor/bin/robo
 *
 * @see  http://robo.li/
 */
require_once 'vendor/autoload.php';

/**
 * Class RoboFile
 *
 * @since  1.5
 */
class RoboFile extends \Robo\Tasks
{
	// Load tasks from composer, see composer.json
	use \redcomponent\robo\loadTasks;

	/**
	 * Current RoboFile version
	 */
	private $version = '1.4';

	/**
	 * Current root folder
	 */
	private $testsFolder = './';

	/**
	 * Hello World example task.
	 *
	 * @see  https://github.com/redCOMPONENT-COM/robo/blob/master/src/HelloWorld.php
	 * @link https://packagist.org/packages/redcomponent/robo
	 *
	 * @return object Result
	 */
	public function sayHelloWorld()
	{
		$result = $this->taskHelloWorld()->run();

		return $result;
	}

	/**
	 * Sends Codeception errors to Slack
	 *
	 * @param   string  $slackChannel             The Slack Channel ID
	 * @param   string  $slackToken               Your Slack authentication token.
	 * @param   string  $codeceptionOutputFolder  Optional. By default tests/_output
	 *
	 * @return mixed
	 */
	public function sendCodeceptionOutputToSlack($slackChannel, $slackToken = null, $codeceptionOutputFolder = null)
	{
		if (is_null($slackToken))
		{
			$this->say('we are in Travis environment, getting token from ENV');

			// Remind to set the token in repo Travis settings,
			// see: http://docs.travis-ci.com/user/environment-variables/#Using-Settings
			$slackToken = getenv('SLACK_ENCRYPTED_TOKEN');
		}

		if (is_null($codeceptionOutputFolder))
		{
			$this->codeceptionOutputFolder = '_output';
		}

		$this->say($codeceptionOutputFolder);

		$result = $this
			->taskSendCodeceptionOutputToSlack(
				$slackChannel,
				$slackToken,
				$codeceptionOutputFolder
			)
			->run();

		return $result;
	}

	/**
	 * Downloads and prepares a Joomla CMS site for testing
	 *
	 * @return mixed
	 */
	public function prepareSiteForSystemTests()
	{
		// Get Joomla Clean Testing sites
		if (is_dir('joomla-cms3'))
		{
			$this->taskDeleteDir('joomla-cms3')->run();
		}

		$version = 'staging';

		/*
		 * When joomla Staging branch has a bug you can uncomment the following line as a tmp fix for the tests layer.
		 * Use as $version value the latest tagged stable version at: https://github.com/joomla/joomla-cms/releases
		 */
		$version = '3.4.5';

		$this->_exec("git clone -b $version --single-branch --depth 1 https://github.com/joomla/joomla-cms.git joomla-cms3");

		$this->say("Joomla CMS ($version) site created at joomla-cms3/");
	}

	/**
	 * Executes Selenium System Tests in your machine
	 *
	 * @param   array  $options  Use -h to see available options
	 *
	 * @return mixed
	 */
	public function runTest($options = [
		'test'         => null,
		'suite'         => 'acceptance',
		'selenium_path' => null
	])
	{
		if (!$options['selenium_path'])
		{
			$this->getSelenium();
		}

		$this->getComposer();

		$this->taskComposerInstall()->run();

		$this->runSelenium($options['selenium_path']);

		$this->taskWaitForSeleniumStandaloneServer()
		     ->run()
		     ->stopOnFail();

		// Make sure to Run the Build Command to Generate AcceptanceTester
		$this->_exec("vendor/bin/codecept build");

		if (!$options['test'])
		{
			$this->say('Available tests in the system:');

			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(
						$this->testsFolder . $options['suite'],
					RecursiveDirectoryIterator::SKIP_DOTS),
				RecursiveIteratorIterator::SELF_FIRST);

			$tests = array();

			$iterator->rewind();
			$i = 1;

			while ($iterator->valid())
			{
				if (strripos($iterator->getSubPathName(), 'cept.php')
					|| strripos($iterator->getSubPathName(), 'cest.php'))
				{
					$this->say('[' . $i . '] ' . $iterator->getSubPathName());
					$tests[$i] = $iterator->getSubPathName();
					$i++;
				}

				$iterator->next();
			}

			$this->say('');
			$testNumber     = $this->ask('Type the number of the test  in the list that you want to run...');
			$options['test'] = $tests[$testNumber];
		}

		$pathToTestFile = $this->testsFolder . $options['suite'] . '/' . $options['test'];

		$this->taskCodecept()
		     ->test($pathToTestFile)
		     ->arg('--steps')
		     ->arg('--debug')
		     ->run()
		     ->stopOnFail();

		$this->killSelenium();
	}

	/**
	 * Preparation for running manual tests after installing Joomla/Extension and some basic configuration
	 *
	 * @return void
	 */
	public function runTestPreparation()
	{
		$this->prepareSiteForSystemTests();

		$this->getSelenium();

		$this->getComposer();

		$this->taskComposerInstall()->run();

		$this->runSelenium();

		$this->taskWaitForSeleniumStandaloneServer()
			->run()
			->stopOnFail();

		// Make sure to Run the Build Command to Generate AcceptanceTester
		$this->_exec("vendor/bin/codecept build");

		$this->taskCodecept()
			->arg('--steps')
			->arg('--debug')
			->arg('--tap')
			->arg('--fail-fast')
			->arg($this->testsFolder . 'acceptance/install/')
			->run()
			->stopOnFail();
	}

	/**
	 * Function to Run tests in a Group
	 *
	 * @param   array  $options  Array of options
	 *
	 * @return void
	 */
	public function runTests($options = ['selenium_path' => null])
	{
		$this->prepareSiteForSystemTests();

		if (!$options['selenium_path'])
		{
			$this->getSelenium();
		}

		//$this->prepareReleasePackages();

		$this->getComposer();

		$this->taskComposerInstall()->run();

		$this->runSelenium($options['selenium_path']);

		$this->taskWaitForSeleniumStandaloneServer()
			->run()
			->stopOnFail();

		// Make sure to Run the Build Command to Generate AcceptanceTester
		$this->_exec("vendor/bin/codecept build");

		$this->taskCodecept()
		     ->arg('--steps')
		     ->arg('--debug')
		     ->arg('--fail-fast')
		     ->arg($this->testsFolder . 'acceptance/install/')
		     ->run()
		     ->stopOnFail();

		$this->taskCodecept()
		     ->arg('--steps')
		     ->arg('--debug')
		     ->arg('--fail-fast')
		     ->arg($this->testsFolder . 'acceptance/administrator/')
		     ->run()
		     ->stopOnFail();

		$this->taskCodecept()
		     ->arg('--steps')
		     ->arg('--debug')
		     ->arg('--fail-fast')
		     ->arg('api')
		     ->run()
		     ->stopOnFail();

		$this->taskCodecept()
		     ->arg('--steps')
		     ->arg('--debug')
		     ->arg('--fail-fast')
		     ->arg($this->testsFolder . 'acceptance/uninstall/')
		     ->run()
		     ->stopOnFail();

		$this->killSelenium();
	}

	/**
	 * This function ensures that you have the latest version of RoboFile in your project.
	 * All redCOMPONENT RoboFiles are clones. All special needs for a project are stored in a robofile.yml file
	 *
	 * @return void
	 */
	public function checkRoboFileVersion()
	{
		$this->taskCheckRoboFileVersion($this->version)
		     ->run()
		     ->stopOnFail();
	}

	/**
	 * Downloads Selenium Standalone Server
	 *
	 * @return void
	 */
	private function getSelenium()
	{
		if (!file_exists('selenium-server-standalone.jar'))
		{
			$this->say('Downloading Selenium Server, this may take a while.');
			$this->_exec('curl'
			             . ' -sS'
			             . ' --retry 3 --retry-delay 5'
			             . ' http://selenium-release.storage.googleapis.com/2.46/selenium-server-standalone-2.46.0.jar'
			             . ' > selenium-server-standalone.jar');
		}
	}

	/**
	 * Stops Selenium Standalone Server
	 *
	 * @return void
	 */
	private function killSelenium()
	{
		$this->_exec('curl http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer');
	}

	/**
	 * Downloads Composer
	 *
	 * @return void
	 */
	private function getComposer()
	{
		// Make sure we have Composer
		if (!file_exists('./composer.phar'))
		{
			$this->_exec('curl --retry 3 --retry-delay 5 -sS https://getcomposer.org/installer | php');
		}
	}

	/**
	 * Runs Selenium Standalone Server
	 *
	 * @param   string  $path  Optional path to selenium standalone server
	 *
	 * @return void
	 */
	private function runSelenium($path = null)
	{
		if (!$path)
		{
			$path = 'selenium-server-standalone.jar';
		}

		// Running Selenium server
		$this->_exec("java -jar $path >> selenium.log 2>&1 &");
	}

	/**
	 * Prepares the .zip packages of the extension to be installed in Joomla
	 */
	public function prepareReleasePackages()
	{
		$this->_exec("gulp release --skip-version --gulpfile ../build/gulpfile.js");
	}

	/**
	 * Looks for PHP Parse errors in core
	 */
	public function checkForParseErrors()
	{
		$this->_exec('php checkers/phppec.php ../extensions/components/com_redcore/ ../extensions/libraries/ ../extensions/modules/ ../extensions/plugins/');
	}

	/**
	 * Looks for missed debug code like var_dump or console.log
	 */
	public function checkForMissedDebugCode()
	{
		$this->_exec('php checkers/misseddebugcodechecker.php ../extensions/components/com_redcore/ ../extensions/libraries/ ../extensions/modules/ ../extensions/plugins/');
	}

	/**
	 * Check the code style of the project against a passed sniffers
	 */
	public function checkCodestyle()
	{
		if (!is_dir('checkers/phpcs/Joomla'))
		{
			$this->say('Downloading Joomla Coding Standards Sniffers');
			$this->_exec("git clone -b master --single-branch --depth 1 https://github.com/joomla/coding-standards.git checkers/phpcs/Joomla");
		}

		$this->taskExec('php checkers/phpcs.php')
				->printed(true)
				->run();
	}
}
