<?php
namespace OutlineApiClient;

use OutlineApiClient\Exceptions\OutlineApiException;
use OutlineApiClient\Exceptions\OutlineKeyException;
use OutlineApiClient\Exceptions\OutlineKeyNotFoundException;

class OutlineKey
{
    protected ?OutlineApiClient $api = null;
    protected array $data = [
        'id' => -1,
        'name' => '',
        'password' => '',
        'port' => -1,
        'method' => '',
        'accessUrl' => ''
    ];

    protected bool $isLoaded = false;


    /**
     * @throws OutlineApiException
     */
    public function __construct($server)
    {
        $this->api = new OutlineApiClient($server);
    }

    protected function setData($setData)
    {
        $this->data = array_merge($this->data, $setData);
    }

    public function getData(): array
    {
        return $this->data;
    }


    protected function isLoaded(): bool
    {
        return $this->isLoaded;
    }

    /**
     * @throws OutlineKeyException
     * @throws OutlineKeyNotFoundException|OutlineApiException
     */
    public function get($keyId, $searchKey = 'id'): array
    {
        $getKeyList = $this->api->getKeys();
        $findKeyData = [];

        if (!empty($getKeyList)) {
            $list = $getKeyList['accessKeys'];

            foreach ($list as $item) {

                if ($keyId == $item[$searchKey]) {
                    $findKeyData = $item;
                    break;
                }
            }

            if (empty($findKeyData)) {
                throw new OutlineKeyNotFoundException('Key not found. You may create new key');
            }

        } else {
            throw new OutlineKeyException('Not transferred keys list');
        }

        return $findKeyData;
    }


    /**
     * @throws OutlineKeyNotFoundException
     * @throws OutlineKeyException
     * @throws OutlineApiException
     */
    public function getByName($name): array
    {
        return $this->get($name, 'name');
    }

    /**
     * @throws OutlineKeyException
     * @throws OutlineKeyNotFoundException
     * @throws OutlineApiException
     */
    public function load($keyId): OutlineKey
    {
        $data = $this->get($keyId);
        $this->setData($data);
        $this->isLoaded = true;

        return $this;
    }

    public function getId()
    {
        return $this->data['id'];
    }

    public function getName()
    {
        return $this->data['name'];
    }


    /**
     * @throws OutlineApiException
     */
    public function getTransfer()
    {
        $transfer = 0;

        $transferData = $this->api->metricsTransfer();

        if (
            isset($transferData['bytesTransferredByUserId'])
            && array_key_exists($this->getId(), $transferData['bytesTransferredByUserId'])
        ) {
            $transfer = $transferData['bytesTransferredByUserId'][$this->getId()];
        }

        return $transfer;
    }

    public function getLimit()
    {
        return $this->data['dataLimit']['bytes'];
    }

    public function getAccessUrl()
    {
        return $this->data['accessUrl'];
    }


    /**
     * @throws OutlineKeyException
     * @throws OutlineApiException
     */
    public function rename($newName)
    {
        if ($this->isLoaded()) {
            $setName = $this->api->setName($this->getId(), $newName);
            if (!$setName) {
                throw new OutlineKeyException('Error rename. Please contact administrator');
            } else {
                $this->setData(['name' => $newName]);
            }
        } else {
            throw new OutlineKeyException('Failed rename key. Need load data key');
        }
    }

    /**
     * @throws OutlineKeyException
     * @throws OutlineApiException
     */
    public function limit($limitValue)
    {
        if ($this->isLoaded()) {
            $setLimit = $this->api->setLimit($this->getId(), $limitValue);

            if (!$setLimit) {
                throw new OutlineKeyException('Error set limit. Please contact administrator');
            } else {
                $this->setData([
                    'dataLimit' => [
                        'bytes' => $limitValue
                    ]
                ]);
            }
        } else {
            throw new OutlineKeyException('Failed set limit for key. Need load data key');
        }
    }

    /**
     * @throws OutlineKeyException
     * @throws OutlineApiException
     */
    public function deleteLimit()
    {
        if ($this->isLoaded()) {
            $deleteLimit = $this->api->delete($this->getId());

            if (!$deleteLimit) {
                throw new OutlineKeyException('Error delete key limit');
            } else {
                $this->setData([
                    'dataLimit' => []
                ]);
            }
        } else {
            throw new OutlineKeyException('Failed delete limit for key. Need load data key');
        }
    }

    /**
     * @throws OutlineKeyException
     * @throws OutlineApiException
     */
    public function create($name, $limit = false): OutlineKey
    {
        if (!empty($name)) {
            $create = $this->api->create();

            if (!empty($create)) {
                $this->setData($create);

                $setName = $this->api->setName($create['id'], $name);

                if ($setName) {
                    $this->setData(['name' => $name]);

                    if ($limit !== false) {
                        $setLimit = $this->api->setLimit($create['id'], $limit);

                        if ($setLimit) {
                            $this->setData([
                                'dataLimit' => [
                                    'bytes' => $limit
                                ]
                            ]);
                        } else {
                            throw new OutlineKeyException('Error set limit key');
                        }
                    }
                } else {
                    throw new OutlineKeyException('Error set key name');
                }
            } else {
                throw new OutlineKeyException('Error create key');
            }
        }

        return $this;
    }


    /**
     * @throws OutlineKeyException
     * @throws OutlineApiException
     */
    public function delete(): bool
    {
        if ($this->api->delete($this->getId())) {
            return true;
        } else {
            throw new OutlineKeyException('Error delete key id=' . $this->getId());
        }
    }
}