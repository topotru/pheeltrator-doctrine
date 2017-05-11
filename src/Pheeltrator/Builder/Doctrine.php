<?php
/**
 * Created by PhpStorm.
 * User: topot
 * Date: 11.05.2017
 * Time: 18:29
 */

namespace TopoTrue\Pheeltrator\Query\Builder;



use Doctrine\DBAL\Query\QueryBuilder;
use TopoTrue\Pheeltrator\Query\Source\Join;

/**
 * Class Doctrine
 * @package TopoTrue\Pheeltrator\Query\Builder
 */
class Doctrine implements BuilderInterface
{
    /**
     * @var QueryBuilder
     */
    protected $builder;
    
    /**
     * @var array
     */
    protected $binds = [];
    
    /**
     * DoctrinePheeltratorBuilder constructor.
     * @param QueryBuilder $builder
     */
    public function __construct(QueryBuilder $builder)
    {
        $this->builder = $builder;
    }
    
    /**
     * @param string|array $columns
     * @return BuilderInterface
     */
    public function select($columns)
    {
        $this->builder->select($columns);
        return $this;
    }
    
    /**
     * @param string $from
     * @param string $alias
     * @return BuilderInterface
     */
    public function from($from, $alias = null)
    {
        $this->builder->from($from, $alias);
        return $this;
    }
    
    /**
     * @param string $source
     * @param string $conditions
     * @param string $alias
     * @param string $type
     * @return BuilderInterface
     */
    public function join($from, $source, $conditions = null, $alias = null, $type = null)
    {
        switch ($type) {
            case Join::LEFT:
                $this->builder->leftJoin($from, $source, $alias, $conditions);
                break;
            case Join::RIGHT:
                $this->builder->rightJoin($from, $source, $alias, $conditions);
                break;
            default:
                $this->builder->join($from, $source, $alias, $conditions);
        }
        return $this;
    }
    
    /**
     * @param string $cond
     * @param array $bindParams
     * @param array $bindTypes
     * @return mixed
     */
    public function andWhere($cond, $bindParams = null, $bindTypes = null)
    {
        $this->builder->andWhere($cond);
        if (is_array($bindParams) && $bindParams) {
            $this->binds = array_merge($this->binds, $bindParams);
        }
        return $this;
    }
    
    /**
     * @param array $binds
     * @return mixed
     */
    public function execute(array $binds = [])
    {
        if (is_array($binds) && $binds) {
            $this->binds = array_merge($this->binds, $binds);
        }
        
        if ($this->binds) {
            $this->builder->setParameters($this->binds);
        }
        //print_r($this->binds);
        //die($this->builder->getSQL());
        $stmt = $this->builder->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * @param string $field
     * @return int
     */
    public function count($field = '*')
    {
        $this->builder->select("COUNT({$field})");
        if ($this->binds) {
            $this->builder->setParameters($this->binds);
        }
        $stmt = $this->builder->execute();
        
        return $stmt->rowCount() > 1 ? $stmt->rowCount() : (int)$stmt->fetchColumn();
    }
    
    /**
     * @param int $limit
     * @param int $offset
     * @return BuilderInterface
     */
    public function limit($limit, $offset = 0)
    {
        $this->builder->setMaxResults($limit);
        $this->builder->setFirstResult($offset);
        return $this;
    }
    
    /**
     * @param string $orderBy
     * @param string $direction
     * @return BuilderInterface
     */
    public function orderBy($orderBy, $direction)
    {
        $this->builder->addOrderBy($orderBy, $direction);
        return $this;
    }
    
    /**
     * @param string $groupBy
     * @return BuilderInterface
     */
    public function groupBy($groupBy)
    {
        $this->builder->groupBy($groupBy);
        return $this;
    }
}

