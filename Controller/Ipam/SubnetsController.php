<?php

namespace Modules\Addons\CustomModule\Controller\Ipam;
use Modules\Addons\CustomModule\Controller\OutputController;
use Modules\Addons\IPManager\Model\IpamSubnet;
use Modules\Addons\IPManager\Repositories\IpamSubnetsRepository;

class SubnetsController extends OutputController
{
    /**
     * IpamSubnetsRepository
     *
     * @var IpamSubnetsRepository
     */
    protected $ipamSubnetsRepository;

    /**
     * @param \Modules\Addons\IPManager\Repositories\IpamSubnetsRepository $ipamSubnetsRepository
     */
    public function __construct(IpamSubnetsRepository $ipamSubnetsRepository)
    {
        $this->ipamSubnetsRepository = $ipamSubnetsRepository;

        parent::__construct();
    }

    /**
     * List IPAM Subnets
     *
     * @return string
     */
    public function listSubnets()
    {
        $subnets = $this->ipamSubnetsRepository->fetchFiltered();

        return response_json([
            'status' => 'success',
            'data' => $subnets->get()
        ]);
    }

    /**
     * Update Empty Gateways
     *
     * @return string
     */
    public function updateEmptyGateways()
    {
        $subnets = $this->ipamSubnetsRepository->fetchFiltered()
            ->where('parent_id', 0)
            ->where('gateway', '')
            ->where('type', 'ipv4')
            ->get();

        /**
         * @var IpamSubnet $subnet 
         */
        foreach($subnets as $subnet)
        {
            if(empty($subnet->getAttribute('gateway'))) {
                $subnetIp = $subnet->getAttribute('pool');

                $gatewayLog = ip2long($subnetIp) + 1;
                $gateway    = long2ip($gatewayLog);

                if (filter_var($gateway, FILTER_VALIDATE_IP)) {
                    echo vsprintf('Subnet: <b>%s</b> - new gateway: <b>%s</b></br>', [
                        $subnet->getLabeledNameAttribute(),
                        $gateway
                    ]);

                    \DB::table('ipam_ip_pools')->where('id', $subnet->getAttribute('id'))->update(['gateway' => $gateway]);
                }
            }
        }
    }
}