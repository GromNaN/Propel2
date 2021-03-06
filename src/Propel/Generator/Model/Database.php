<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Propel\Generator\Model;

use Propel\Generator\Exception\EngineException;
use Propel\Generator\Exception\InvalidArgumentException;
use Propel\Generator\Platform\PlatformInterface;

/**
 * A class for holding application data structures.
 *
 * @author Hans Lellelid <hans@xmpl.org> (Propel)
 * @author Leon Messerschmidt <leon@opticode.co.za> (Torque)
 * @author John McNally<jmcnally@collab.net> (Torque)
 * @author Martin Poeschl<mpoeschl@marmot.at> (Torque)
 * @author Daniel Rall<dlr@collab.net> (Torque)
 * @author Byron Foster <byron_foster@yahoo.com> (Torque)
 * @author Hugo Hamon <webmaster@apprendre-php.com> (Torque)
 */
class Database extends ScopedMappingModel
{
    const DEFAULT_STRING_FORMAT = 'YAML';

    private $platform;
    private $tables;
    private $name;
    private $baseClass;
    private $basePeer;
    private $defaultIdMethod;
    private $defaultPhpNamingMethod;
    private $defaultTranslateMethod;
    private $domainMap;
    private $heavyIndexing;
    private $parentSchema;
    private $tablesByName;
    private $tablesByLowercaseName;
    private $tablesByPhpName;

    protected $behaviors;
    protected $defaultStringFormat;
    protected $tablePrefix;

    /**
     * Constructs a new Database object.
     *
     * @param string $name
     */
    public function __construct($name = null)
    {
        parent::__construct();

        if (null !== $name) {
            $this->setName($name);
        }

        $this->heavyIndexing = false;
        $this->tablePrefix = '';
        $this->defaultPhpNamingMethod = NameGenerator::CONV_METHOD_UNDERSCORE;
        $this->defaultIdMethod = IdMethod::NATIVE;
        $this->defaultStringFormat = static::DEFAULT_STRING_FORMAT;
        $this->behaviors = array();
        $this->domainMap = array();
        $this->tables = array();
        $this->tablesByName = array();
        $this->tablesByPhpName = array();
        $this->tablesByLowercaseName = array();
    }

    protected function setupObject()
    {
        parent::setupObject();

        $this->name = $this->getAttribute('name');
        $this->baseClass = $this->getAttribute('baseClass');
        $this->basePeer = $this->getAttribute('basePeer');
        $this->defaultIdMethod = $this->getAttribute('defaultIdMethod', IdMethod::NATIVE);
        $this->defaultPhpNamingMethod = $this->getAttribute('defaultPhpNamingMethod', NameGenerator::CONV_METHOD_UNDERSCORE);
        $this->heavyIndexing = $this->booleanValue($this->getAttribute('heavyIndexing'));
        $this->tablePrefix = $this->getAttribute('tablePrefix', $this->getBuildProperty('tablePrefix'));
        $this->defaultStringFormat = $this->getAttribute('defaultStringFormat', static::DEFAULT_STRING_FORMAT);
    }

    /**
     * Returns the PlatformInterface implementation for this database.
     *
     * @return PlatformInterface
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Sets the PlatformInterface implementation for this database.
     *
     * @param PlatformInterface $platform A Platform implementation
     */
    public function setPlatform(PlatformInterface $platform = null)
    {
        $this->platform = $platform;
    }

    /**
     * Returns the database name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the database name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the name of the base super class inherited by active record
     * objects. This parameter is overridden at the table level.
     *
     * @return string
     */
    public function getBaseClass()
    {
        return $this->baseClass;
    }

    /**
     * Sets the name of the base super class inherited by active record objects.
     * This parameter is overridden at the table level.
     *
     * @param string $class.
     */
    public function setBaseClass($class)
    {
        $this->baseClass = $class;
    }

    /**
     * Returns the name of the base peer super class inherited by Peer classes.
     * This parameter is overridden at the table level.
     *
     * @return string
     */
    public function getBasePeer()
    {
        return $this->basePeer;
    }

    /**
     * Sets the name of the base peer super class inherited by Peer classes.
     * This parameter is overridden at the table level.
     *
     * @param string $class
     */
    public function setBasePeer($class)
    {
        $this->basePeer = $class;
    }

    /**
     * Returns the name of the default ID method strategy.
     * This parameter can be overridden at the table level.
     *
     * @return string
     */
    public function getDefaultIdMethod()
    {
        return $this->defaultIdMethod;
    }

    /**
     * Sets the name of the default ID method strategy.
     * This parameter can be overridden at the table level.
     *
     * @param string $strategy
     */
    public function setDefaultIdMethod($strategy)
    {
        $this->defaultIdMethod = $strategy;
    }

    /**
     * Returns the name of the default PHP naming method strategy, which
     * specifies the method for converting schema names for table and column to
     * PHP names. This parameter can be overridden at the table layer.
     *
     * @return string
     */
    public function getDefaultPhpNamingMethod()
    {
        return $this->defaultPhpNamingMethod;
    }

    /**
     * Sets name of the default PHP naming method strategy.
     *
     * @param string $strategy
     */
    public function setDefaultPhpNamingMethod($strategy)
    {
        $this->defaultPhpNamingMethod = $strategy;
    }

    /**
     * Returns the list of supported string formats
     *
     * @return array
     */
    public static function getSupportedStringFormats()
    {
        return array('XML', 'YAML', 'JSON', 'CSV');
    }

    /**
     * Sets the default string format for ActiveRecord objects in this table.
     * This parameter can be overridden at the table level.
     *
     * Any of 'XML', 'YAML', 'JSON', or 'CSV'.
     *
     * @param  string                   $format
     * @throws InvalidArgumentException
     */
    public function setDefaultStringFormat($format)
    {
        $formats = static::getSupportedStringFormats();

        $format = strtoupper($format);
        if (!in_array($format, $formats)) {
            throw new InvalidArgumentException(sprintf('Given "%s" default string format is not supported. Only "%s" are valid string formats.', $format, implode(', ', $formats)));
        }

        $this->defaultStringFormat = $format;
    }

    /**
     * Returns the default string format for ActiveRecord objects in this table.
     * This parameter can be overridden at the table level.
     *
     * @return string
     */
    public function getDefaultStringFormat()
    {
        return $this->defaultStringFormat;
    }

    /**
     * Returns whether or not heavy indexing is enabled.
     *
     * This is an alias for getHeavyIndexing().
     *
     * @return boolean
     */
    public function isHeavyIndexing()
    {
        return $this->getHeavyIndexing();
    }

    /**
     * Returns whether or not heavy indexing is enabled.
     *
     * This is an alias for isHeavyIndexing().
     *
     * @return boolean
     */
    public function getHeavyIndexing()
    {
        return $this->heavyIndexing;
    }

    /**
     * Sets whether or not heavy indexing is enabled.
     *
     * @param boolean $heavyIndexing
     */
    public function setHeavyIndexing($heavyIndexing)
    {
        $this->heavyIndexing = (Boolean) $heavyIndexing;
    }

    /**
     * Return the list of all tables.
     *
     * @return array
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * Return the number of tables in the database.
     *
     * @return integer
     */
    public function countTables()
    {
        $count = 0;
        foreach ($this->tables as $table) {
            if (!$table->isReadOnly()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Returns the list of all tables that have a SQL representation.
     *
     * @return array
     */
    public function getTablesForSql()
    {
        $tables = array();
        foreach ($this->tables as $table) {
            if (!$table->isSkipSql()) {
                $tables[] = $table;
            }
        }

        return $tables;
    }

    /**
     * Returns whether or not the database has a table.
     *
     * @param  string  $name
     * @param  boolean $caseInsensitive
     * @return boolean
     */
    public function hasTable($name, $caseInsensitive = false)
    {
        if ($caseInsensitive) {
            return isset($this->tablesByLowercaseName[ strtolower($name) ]);
        }

        return isset($this->tablesByName[$name]);
    }

    /**
     * Returns the table with the specified name.
     *
     * @param  string  $name
     * @param  boolean $caseInsensitive
     * @return Table
     */
    public function getTable($name, $caseInsensitive = false)
    {
        if (!$this->hasTable($name, $caseInsensitive)) {
            return null;
        }

        if ($caseInsensitive) {
            return $this->tablesByLowercaseName[strtolower($name)];
        }

        return $this->tablesByName[$name];
    }

    /**
     * Returns whether or not the database has a table identified by its
     * PHP name.
     *
     * @param  string  $phpName
     * @return boolean
     */
    public function hasTableByPhpName($phpName)
    {
        return isset($this->tablesByPhpName[$phpName]);
    }

    /**
     * Returns the table object with the specified PHP name.
     *
     * @param  string $phpName
     * @return Table
     */
    public function getTableByPhpName($phpName)
    {
        if (isset($this->tablesByPhpName[$phpName])) {
            return $this->tablesByPhpName[$phpName];
        }

        return null; // just to be explicit
    }

    /**
     * Adds a new table to this database.
     *
     * @param  Table|array $table
     * @return Table
     */
    public function addTable($table)
    {
        if (!($table instanceof Table)) {
            $tbl = new Table();
            $tbl->setDatabase($this);
            $tbl->setSchema($this->getSchema());
            $tbl->loadMapping($table);

            return $this->addTable($tbl);
        }

        $table->setDatabase($this);

        if (isset($this->tablesByName[$table->getName()])) {
            throw new EngineException(sprintf('Table "%s" declared twice', $table->getName()));
        }

        if (null === $table->getSchema()) {
            $table->setSchema($this->getSchema());
        }

        $this->tables[] = $table;
        $this->tablesByName[$table->getName()] = $table;
        $this->tablesByLowercaseName[strtolower($table->getName())] = $table;
        $this->tablesByPhpName[$table->getPhpName()] = $table;

        $this->computeTableNamespace($table);

        if (null === $table->getPackage()) {
            $table->setPackage($this->getPackage());
        }

        return $table;
    }

    /**
     * Computes the table namespace based on the current relative or
     * absolute table namespace and the database namespace.
     *
     * @param  Table  $table
     * @return string
     */
    private function computeTableNamespace(Table $table)
    {
        $namespace = $table->getNamespace();
        if ($this->isAbsoluteNamespace($namespace)) {
            $namespace = ltrim($namespace, '\\');
            $table->setNamespace($namespace);

            return $namespace;
        }

        if ($namespace = $this->getNamespace()) {
            if ($table->getNamespace()) {
                $namespace .= '\\'.$table->getNamespace();
            }

            $table->setNamespace($namespace);
        }

        return $namespace;
    }

    /**
     * Sets the parent schema
     *
     * @param Schema $parent The parent schema
     */
    public function setParentSchema(Schema $parent)
    {
        $this->parentSchema = $parent;
    }

    /**
     * Returns the parent schema
     *
     * @return Schema
     */
    public function getParentSchema()
    {
        return $this->parentSchema;
    }

    /**
     * Adds a domain object to this database.
     *
     * @param  Domain|array $data
     * @return Domain
     */
    public function addDomain($data)
    {
        if ($data instanceof Domain) {
            $domain = $data; // alias
            $domain->setDatabase($this);
            $this->domainMap[$domain->getName()] = $domain;

            return $domain;
        }

        $domain = new Domain();
        $domain->setDatabase($this);
        $domain->loadMapping($data);

        return $this->addDomain($domain); // call self w/ different param
    }

    /**
     * Returns the already configured domain object by its name.
     *
     * @param  string $name
     * @return Domain
     */
    public function getDomain($name)
    {
        if (isset($this->domainMap[$name])) {
            return $this->domainMap[$name];
        }

        return null;
    }

    /**
     * Returns the GeneratorConfigInterface object.
     *
     * @return GeneratorConfigInterface
     */
    public function getGeneratorConfig()
    {
        if ($this->parentSchema) {
            return $this->parentSchema->getGeneratorConfig();
        }

        return null;
    }

    /**
     * Returns the build property identified by its name.
     *
     * @param  string $name
     * @return string
     */
    public function getBuildProperty($name)
    {
        if ($config = $this->getGeneratorConfig()) {
            return $config->getBuildProperty($name);
        }

        return null;
    }

    /**
     * Adds a new behavior to the database*
     *
     * @param  Behavior|array $bdata
     * @return Behavior
     */
    public function addBehavior($bdata)
    {
        if ($bdata instanceof Behavior) {
            $behavior = $bdata;
            $behavior->setDatabase($this);
            $this->behaviors[$behavior->getName()] = $behavior;

            return $behavior;
        }

        $class = $this->getConfiguredBehavior($bdata['name']);
        $behavior = new $class();
        $behavior->loadMapping($bdata);

        return $this->addBehavior($behavior);
    }

    /**
     * Returns the list of all database behaviors.
     *
     * @return array
     */
    public function getBehaviors()
    {
        return $this->behaviors;
    }

    /**
     * Returns whether or not the database has a specific behavior.
     *
     * @param  string  $name
     * @return boolean
     */
    public function hasBehavior($name)
    {
        return isset($this->behaviors[$name]);
    }

    /**
     * Returns the corresponding behavior identified by its name.
     *
     * @param  string   $name
     * @return Behavior
     */
    public function getBehavior($name)
    {
        if (isset($this->behaviors[$name])) {
            return $this->behaviors[$name];
        }

        return null;
    }

    /**
     * Returns the table prefix for this database.
     *
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * Returns the next behavior on all tables, ordered by behavior priority,
     * and skipping the ones that were already executed.
     *
     * @return Behavior
     */
    public function getNextTableBehavior()
    {
        // order the behaviors according to Behavior::$tableModificationOrder
        $behaviors = array();
        $nextBehavior = null;
        foreach ($this->tables as $table) {
            foreach ($table->getBehaviors() as $behavior) {
                if (!$behavior->isTableModified()) {
                    $behaviors[$behavior->getTableModificationOrder()][] = $behavior;
                }
            }
        }
        ksort($behaviors);
        if (count($behaviors)) {
            $nextBehavior = $behaviors[key($behaviors)][0];
        }

        return $nextBehavior;
    }

    /**
     * Finalizes the setup process.
     *
     */
    public function doFinalInitialization()
    {
        // add the referrers for the foreign keys
        $this->setupTableReferrers();

        // add default behaviors to database
        if ($defaultBehaviors = $this->getBuildProperty('behaviorDefault')) {
            // add generic behaviors from build.properties
            $defaultBehaviors = explode(',', $defaultBehaviors);
            foreach ($defaultBehaviors as $behavior) {
                $this->addBehavior(array('name' => trim($behavior)));
            }
        }

        // execute database behaviors
        foreach ($this->getBehaviors() as $behavior) {
            $behavior->modifyDatabase();
        }

        // execute table behaviors (may add new tables and new behaviors)
        while ($behavior = $this->getNextTableBehavior()) {
            $behavior->getTableModifier()->modifyTable();
            $behavior->setTableModified(true);
        }

        // do naming and heavy indexing
        foreach ($this->tables as $table) {
            $table->doFinalInitialization();
            // setup referrers again, since final initialization may have added columns
            $table->setupReferrers(true);
        }
    }

    /**
     * Setups all table referrers.
     *
     */
    protected function setupTableReferrers()
    {
        foreach ($this->tables as $table) {
            $table->doNaming();
            $table->setupReferrers();
        }
    }

    public function appendXml(\DOMNode $node)
    {
        $doc = ($node instanceof \DOMDocument) ? $node : $node->ownerDocument;

        $dbNode = $node->appendChild($doc->createElement('database'));

        $dbNode->setAttribute('name', $this->name);

        if ($this->package) {
            $dbNode->setAttribute('package', $this->package);
        }

        if ($this->defaultIdMethod) {
            $dbNode->setAttribute('defaultIdMethod', $this->defaultIdMethod);
        }

        if ($this->baseClass) {
            $dbNode->setAttribute('baseClass', $this->baseClass);
        }

        if ($this->basePeer) {
            $dbNode->setAttribute('basePeer', $this->basePeer);
        }

        if ($this->defaultPhpNamingMethod) {
            $dbNode->setAttribute('defaultPhpNamingMethod', $this->defaultPhpNamingMethod);
        }

        /*

        FIXME - Before we can add support for domains in the schema, we need
        to have a method of the Column that indicates whether the column was mapped
        to a SPECIFIC domain (since Column->getDomain() will always return a Domain object)

        foreach ($this->domainMap as $domain) {
        $domain->appendXml($dbNode);
        }
         */
        foreach ($this->vendorInfos as $vi) {
            $vi->appendXml($dbNode);
        }

        foreach ($this->tables as $table) {
            $table->appendXml($dbNode);
        }
    }
}
