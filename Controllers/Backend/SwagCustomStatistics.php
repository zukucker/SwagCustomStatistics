<?php

class Shopware_Controllers_Backend_SwagCustomStatistics extends Shopware_Controllers_Backend_ExtJs
{
    public function getVoucherStatisticsAction()
    {
        $connection = $this->container->get('dbal_connection');
        $query = $connection->createQueryBuilder();
        $query->select(['COUNT(codes.cashed) as amount', 'vouchers.description as name'])
            ->from('s_emarketing_voucher_codes', 'codes')
            ->innerJoin('codes', 's_emarketing_vouchers', 'vouchers', 'vouchers.id = codes.voucherID')
            ->where('codes.cashed = 1')
            ->groupBy('vouchers.id');

        $idList = (string) $this->Request()->getParam('selectedShops');
        if (!empty($idList)) {
            $selectedShopIds = explode(',', $idList);

            foreach ($selectedShopIds as $shopId) {
                $query->addSelect('SUM(IF(vouchers.subshopID = ' . $connection->quote($shopId) . ', codes.cashed, 0)) as amount' . $shopId);
            }
        }

        $data = $query->execute()->fetchAll();

        $getdata = $_GET;
        if($getdata["format"] == "csv"){
          $this->exportData($getdata, $data);
        }

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'count' => count($data)
        ]);
    }
    public function exportData($getrequestdata, $data){
      $filename = 'testexport.csv';
      $f = fopen('php://memory', 'w');
      $fields = array('Amount', 'Name'); 
      $delimiter = ";";
      fputcsv($f, $fields, $delimiter);

      foreach($data as $dataset){
        $lineData = array($dataset['amount'], $dataset['name']); 
        //dump($lineData);
        //die();
        fputcsv($f, $lineData, $delimiter); 
      }
      fseek($f, 0); 
     
      // Set headers to download file rather than displayed 
      header('Content-Type: text/csv'); 
      header('Content-Disposition: attachment; filename="' . $filename . '";'); 
       
      //output all remaining data on a file pointer 
      fpassthru($f);
      exit();
    }
}
