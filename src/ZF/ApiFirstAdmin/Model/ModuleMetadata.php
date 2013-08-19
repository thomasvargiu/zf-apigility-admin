<?php

namespace ZF\ApiFirstAdmin\Model;

use InvalidArgumentException;
use ReflectionClass;

class ModuleMetadata
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var bool
     */
    protected $isVendor;

    /**
     * @var array
     */
    protected $restEndpoints;

    /**
     * @var array
     */
    protected $rpcEndpoints;

    /**
     * @param  string $name
     * @param  array $restEndpoints
     * @param  array $rpcEndpoints
     * @param  bool $isVendor
     * @throws InvalidArgumentException for modules that do not exist
     */
    public function __construct($namespace, array $restEndpoints = array(), array $rpcEndpoints = array(), $isVendor = null)
    {
        if (!class_exists($namespace . '\\Module')) {
            throw new InvalidArgumentException(sprintf(
                'Invalid module "%s"; no Module class exists for that module',
                $name
            ));
        }

        $this->name          = $this->normalizeName($namespace);
        $this->namespace     = $namespace;
        $this->restEndpoints = $restEndpoints;
        $this->rpcEndpoints  = $rpcEndpoints;
        $this->isVendor      = is_bool($isVendor) ? $isVendor : null;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return bool
     */
    public function isVendor()
    {
        if (null === $this->isVendor) {
            $this->determineVendorStatus();
        }
        return $this->isVendor;
    }

    /**
     * @return array
     */
    public function getRestEndpoints()
    {
        return $this->restEndpoints;
    }

    /**
     * @return array
     */
    public function getRpcEndpoints()
    {
        return $this->rpcEndpoints;
    }

    /**
     * Populate object from array
     *
     * @param  array $data
     */
    public function exchangeArray(array $data)
    {
        foreach ($data as $key => $value) {
            switch (strtolower($key)) {
                case 'module':
                case 'name':
                    $this->name = $value;
                    break;
                case 'namespace':
                    $this->namespace = $value;
                    break;
                case 'isvendor':
                case 'is_vendor':
                    $this->isVendor = (bool) $value;
                    break;
                case 'rest':
                    if (!is_array($value)) {
                        throw new InvalidArgumentException(
                            'REST endpoints must be an array; received "%s"',
                            (is_object($value) ? get_class($value) : gettype($value))
                        );
                    }
                    $this->restEndpoints = $value;
                    break;
                case 'rpc':
                    if (!is_array($value)) {
                        throw new InvalidArgumentException(
                            'RPC endpoints must be an array; received "%s"',
                            (is_object($value) ? get_class($value) : gettype($value))
                        );
                    }
                    $this->rpcEndpoints = $value;
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Retrieve array representation
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return array(
            'module'    => $this->name,
            'namespace' => $this->namespace,
            'is_vendor' => $this->isVendor(),
            'rest'      => $this->restEndpoints,
            'rpc'       => $this->rpcEndpoints,
        );
    }

    /**
     * Determine whether or not a module is a vendor module
     *
     * Use ReflectionClass to determine the filename, and then checks if the
     * module lives in a vendor subdirectory.
     *
     * @todo   Add other criteria, such as "library"?
     */
    protected function determineVendorStatus()
    {
        $r = new ReflectionClass($this->namespace . '\\Module');
        $filename = $r->getFileName();
        if (preg_match('#[/\\\\]vendor[/\\\\]#', $filename)) {
            $this->isVendor = true;
            return;
        }
        $this->isVendor = false;
    }

    /**
     * normalizeName
     *
     * @param mixed $namespace
     * @return void
     */
    protected function normalizeName($namespace)
    {
        return str_replace('\\', '.', $namespace);
    }

    protected function deriveNamespace($name)
    {
        return str_replace('.', '\\', $namespace);
    }
}
