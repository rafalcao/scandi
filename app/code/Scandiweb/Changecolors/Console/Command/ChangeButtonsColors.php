<?php
    namespace Scandiweb\Changecolors\Console\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;
	use Magento\Framework\App\Filesystem\DirectoryList;
	use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
	use Magento\Store\Api\StoreRepositoryInterface;

	//command example: bin/magento scandiweb:color-change --color 'rgbcode' --storeid $storenumber

    class ChangeButtonsColors extends Command
    {
        const COLOR = 'color';
		const STOREID = 'storeid'; 

		protected $_filesystem;
		private $_configInterface;
		protected $_repository;

		public function __construct(
		   \Magento\Framework\Filesystem $filesystem,
			ConfigInterface $configInterface,
			StoreRepositoryInterface $repository
		) {
		    $this->_filesystem  = $filesystem;
			$this->_configInterface = $configInterface;
			$this->_repository = $repository;

		    parent::__construct('scandiweb:color-change');
		}

 		protected function configure() {
            $this->setName('scandiweb:color-change');
            $this->setDescription('Buttons change color command');
            $this->addOption(
                self::COLOR,
                null,
                InputOption::VALUE_REQUIRED,
                'Name'
            );

			$this->addOption(
                self::STOREID,
                null,
                InputOption::VALUE_REQUIRED,
                'Storeid'
            );

            parent::configure();
        }

        public function execute(InputInterface $input, OutputInterface $output) {

			//get store lists to avoid save non-existent store id value in database
			$stores = $this->_repository->getList();
			$storeIds = array();

			foreach ($stores as $store) {
				$storeIds[] = $store->getId();
			}
			$storeid = $input->getOption(self::STOREID);

            if ($name = $input->getOption(self::COLOR)) { // check if commands sent

				if(in_array($storeid, $storeIds)) { //check if store id exists
	
					if(ctype_xdigit($name)) { //check if rgb valid
						$mediapath  = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath();
						$filePath = $mediapath . 'design/frontend/Scandiweb/scandicolors/web/css/source/_buttons_extend.less';

						// Read the file in as an array of lines
						$fileData = file($filePath);

						$newArray = array();
						foreach($fileData as $line) {
						  // find the line that starts with @scandiColor: and change it to @scandiColor: @var $name
						  if (substr($line, 0, 13) == '@scandiColor:') {
							$line = "@scandiColor:#$name;";
						  }
						  $newArray[] = $line;
						}

						// Overwrite _buttons_extend.less with new values
						$fp = fopen($filePath, 'w');
						$newArray = str_replace(array("\r", "\n"), '', $newArray); //clear extra lines
						fwrite($fp, implode("\n",$newArray));
						fclose($fp);
						
						//scandicolors theme id 4 is our extension of Luma Theme

						$this->_configInterface->saveConfig('design/theme/theme_id', 4, 'stores', $storeid);

						$output->writeln('<info>Colors updated successfully</info>');

					} else {
						$output->writeln('<error>Wrong RGB Format</error>');
					}
				} else {
					$output->writeln('<error>Store ID does not exist</error>');
				}

            }
        }
    }

