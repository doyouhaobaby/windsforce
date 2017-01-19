<?qeephp
class DbSelect{

	/**
	 * 用于初始化一个查询的内容
	 *
	 * @access protected
	 * @var array
	 */
	protected static $_arrOptionsInit=array();

	/**
	 * 可用的集合类型
	 *
	 * @access protected
	 * @var array
	 */
	protected static $_arrAggregateTypes=array();

	/**
	 * 可用的 JOIN 操作类型
	 *
	 * @access protected
	 * @var array
	 */
	protected static $_arrJoinTypes=array();

	/**
	 * 可用的 UNICODE 类型
	 *
	 * @access protected
	 * @var array
	 */
	protected static $_arrUnionTypes=array();

	/**
	 * 查询参数初始化
	 *
	 * @access protected
	 * @var array
	 */
	protected static $_arrQueryParamsInit=array();

	/**
	 * 构造查询的各个部分
	 *
	 * @access protected
	 * @var array
	 */
	protected $_arrOptions=array();

	 /**
	 * 查询参数（仅用于一次查询）
	 *
	 * @var array
	 */
	protected $_arrQueryParams;

	/**
	 * 指示查询上下文中当前的表名称或其别名
	 *
	 * @var string
	 */
	protected $_currentTable;

	/**
	 * 当前查询已经连接的数据表
	 *
	 * @var array
	 */
	protected $_arrJoinedTables=array();

	/**
	 * 字段名映射
	 *
	 * @access protected
	 * @var array
	 */
	protected $_arrColumnsMapping=array();

	/**
	 * 查询ID
	 *
	 * @var int
	 */
	private static $_nQueryId=0;

	/**
	 * 当前查询所服务的 ModelMeta 继承类的元信息对象
	 *
	 * @var ModelMeta
	 */
	protected $_oMeta;

	/**
	 * For update
	 *
	 * @var bool
	 */
	protected $_bForUpdate=false;

	/**
	 * 数据库连接
	 *
	 * @access private
	 * @var DbConnect
	 */
	private $_oConnect;

	/**
	 * SubSQL Group 子句
	 *
	 * @access private
	 * @var DbSubSqlGroup
	 */
	private $_oSubSqlGroup=null;

	/**
	 * SubSQL Group 子句
	 *
	 * @access private
	 * @var string
	 */
	private $_sSubSqlGroup=null;

	/**
	 * SubSQL ReturnColumnList 子句
	 *
	 * @access private
	 * @var DbSubSqlReturnColumnList
	 */
	private $_oSubSqlReturnColumnList=null;

	/**
	 * SubSQL ReturnColumnList 子句
	 *
	 * @access private
	 * @var string
	 */
	private $_sSubSqlReturnColumnList=null;

	/**
	 * SubSQL On 子句
	 *
	 * @access private
	 * @var DbSubSqlOn
	 */
	private $_oSubSqlOn=null;

	/**
	 * SubSQL On 子句
	 *
	 * @access private
	 * @var string
	 */
	private $_sSubSqlOn=null;

	/**
	 * 数据表
	 *
	 * @access public
	 * @param $oFactory DbFactory 对象工厂
	 * @param $oConnect DbConnect 数据库连接
	 * @return void
	 */
	public function __construct(DbFactory $oFactory,DbConnect $oConnect){}

	/**
	 * 设置要使用的数据库访问对象
	 *
	 * @param DbConnect $oConnect 数据库连接对象
	 * @return DbRecordSelect
	 */
	public function setConnect(DbConnect $oConnect){}

	/**
	 * 取回 数据库连接对象
	 *
	 * @access public
	 * @return DbConnect
	 */
	public function getConnect(){}

	/**
	 * 统计符合条件的记录数，并立即返回结果
	 *
	 * @access public
	 * @param string|DbExpression $Field 字段
	 * @param string $sAlias 别名
	 * @return int
	 */
	public function getCounts($Field='*',$sAlias='row_count'){}

	/**
	 * 统计平均值，并立即返回结果
	 *
	 * @access public
	 * @param string|DbExpression $Field 字段
	 * @param string $sAlias 别名
	 * @return int|float
	 */
	public function getAvg($Field,$sAlias='avg_value'){}

	/**
	 * 统计最大值，并立即返回结果
	 *
	 * @access public
	 * @param string|DbExpression $Field 字段
	 * @param string $sAlias 别名
	 * @return int|float
	 */
	public function getMax($Field,$sAlias='max_value'){}

	/**
	 * 统计最小值，并立即返回结果
	 *
	 * @access public
	 * @param string|DbExpression $Field 字段
	 * @param string $sAlias
	 * @return int|float
	 */
	public function getMin($Field,$sAlias='min_value'){}

	/**
	 * 统计合计，并立即返回结果
	 *
	 * @access public
	 * @param string|DbExpress $Field 字段
	 * @param string $sAlias 别名
	 * @return int|float
	 */
	public function getSum($Field,$sAlias='sum_value'){}

	/**
	 * 执行查询并返回指定数量的结果
	 *
	 * @param int $nNum 数量
	 * @param array|string $IncludedLinks
	 * @return mixed
	 */
	public function get($nNum=null,$IncludedLinks=null){}

	/**
	 * 返回符合主键的一个结果
	 *
	 * @access public
	 * @param string|int $Id
	 * @param array|string $IncludedLinks
	 * @return mixed
	 */
	public function getById($Id,$IncludedLinks=null){}

	/**
	 * 仅返回一个结果
	 *
	 * @access public
	 * @param array|string $IncludedLinks
	 * @return mixed
	 */
	public function getOne($IncludedLinks=null){}

	/**
	 * 执行查询并返回所有结果，等同于 ->all()->query()
	 *
	 * @access public
	 * @param array|string $IncludedLinks
	 * @return mixed
	 */
	public function getAll($IncludedLinks=null){}

	/**
	 * 获取一条记录的某个字段值
	 *
	 * @access public
	 * @param string $sColumn 字段名
	 * @param string $sSpea 字段数据间隔符号
	 * @return mixed
	 */
	public function getColumn($sColumn,$sSepa='-'){}

	/**
	 * 执行查询
	 *
	 * < $arrIncludedLinks 用于指定查询时要包含的关联。>
	 *
	 * < 默认情况下，DbSelect 对象会以数组形式返回查询结果。
	 *   在这种模式下，关联的数据会被立即查询出来，并嵌入查询结果中。>
	 *
	 * < 如果指定 DbSelect 以 ActiveRecord 对象返回查询结果，则只有 $arrIncludedLinks 指定的关联会被立即查询。
	 *   否则在第一次访问返回的 Modle 对象的聚合属性时，才会进行关联对象的查询。>
	 *
	 * @param array|string $arrIncludedLinks
	 * @return mixed
	 */
	public function query($arrIncludedLinks=null){}

	/**
	 * 执行查询，返回结果句柄
	 *
	 * @return DbRecordSet
	 */
	public function getQueryHandle(){}

	/**
	 * 魔法方法
	 *
	 * @access public
	 * @param string $sMethod 方法名
	 * @param array $arrArgs 参数
	 * @return mixed
	 */
	public  function __call($sMethod,array $arrArgs){}

	/**
	 * 查询，并返回数组结果
	 *
	 * @param boolean $bCleanUp
	 * @return array
	 */
	protected function queryArray_($bCleanUp=true){}

	/**
	 * 查询，并返回对象或对象集合
	 *
	 * @access protected
	 * @return ModelRelationColl|Model
	 */
	protected function queryObjects_(){}

	/**
	 * 创建一个 SELECT DISTINCT 查询
	 *
	 * <!-- DISTINCT说明 -->
	 *
	 * < 在表中，有时候会包含重复值，这并不成问题，不过有的时候你需要得到不同(distinct)的值 >
	 * < 使用方法:SELECT DISTINCT 列名字 FROM 表名字 >
	 * < SELECT Company FROM Orders >
	 * < SELECT DISTINCT Company FROM Orders >
	 *
	 * @access public
	 * @param bool $flag 指示是否是一个 SELECT DISTINCT 查询（默认 true）
	 * @return DbRecordSelect
	 */
	public function distinct($bFlag=true){}

	/**
	 * 添加一个要查询的表及其要查询的字段
	 *
	 * <!--$Table 参数说明-- >
	 *
	 * < $Table 参数指定要从哪个数据表查询数据。该参数可以是字符串、名值对或者一个表数据入口对象 >
	 * < 如果 $table 是一个字符串，则假定为一个表名称，或者是“表名称 AS 别名” >
	 * < 如果 $Table 是一个表数据入口对象，则表名称由表数据入口对象确定 >
	 * < 如果 $Table 是一个名值对，则键名假定为要在查询中使用的表别名，键值可以是字符串或表数据入口对象 >
	 *
	 *
	 * <!--数据表所属 schema说明-->
	 *
	 * < 如果要指定数据表所属 schema，可以采用如下形式：
	 *   $oSelect->from('schema名.表名称');
	 *   // 或者
	 *   $oSelect->from(array('表别名'=> 'schema名.表名称')); >
	 * < 如果 $table 是一个表数据入口对象，则 schmea 由表数据入口对象确定 >
	 *
	 *
	 * <!--$Cols 参数说明-->
	 *
	 * < $Cols 参数指定了要查询该表的哪些字段，如果不指定则默认为 '*'（既查询所有字段）>
	 * < $Cols 可以是一个以“,”分割的字段名字符串，也可以是一个数组，以及一个 DbExpression表达式对象 >
	 * < 例如：>
	 * <  // SELECT `posts`.`title`,`posts`.`body` FROM `posts`
	 *   $oSelect->from('posts','title,body'); >
	 *
	 * < 还可以为字段名指定查询时使用的别名，例如：>
	 * < // 指定字段别名
	 *   // SELECT `posts`.`title` AS `t` FROM `posts`
	 *   $oSelect->from('posts',array('t'=> 'title')); >
	 *
	 * < $Cols 是字符串或数组时，字段名会被自动转义 >
	 * < 如果 $Cols 是一个 DbExpression 对象，则表达式中的字段名需要使用“[]”来指定转义 >
	 * < // SELECT LEFT(`posts`.`title`,5)FROM `posts`
	 *   $oExpr=new DbExpression('LEFT([title],5)');
	 *   $oSelect->from('posts',$oExpr); >
	 *
	 * <!-- left()函数和charindex()函数的使用的使用方法 -->
	 * < 1、left()LEFT(<character_expression>， <integer_expression>)
	 *   返回character_expression 左起 integer_expression 个字符。
	 *   2.charindex()
	 *   返回字符串中某个指定的子串出现的开始位置。
	 *   CHARINDEX(<’substring_expression’>， <expression>)
	 *   其中substring _expression 是所要查找的字符表达式，expression 可为字符串也可为列名表达式。如果没有发现子串，则返回0 值。
	 *   此函数不能用于TEXT 和IMAGE 数据类型。
	 *   实例：select left(t_cont,CHARINDEX(':',t_cont)-1)as content from test where t_cont like '%:%' >
	 *
	 * < // SELECT LEFT(`p`.`title`,5)FROM `posts` `p`
	 *   $oExpr=new DbExpression('LEFT([title],5)');
	 *   $oSelect->from(array('p'=> 'posts'),$expr); >
	 *
	 * < from()的更多用法：>
	 *
	 * < // 指定要查询的数据表及字段
	 *   $oSelect->from('posts','title,body'); >
	 *
	 * < // 为数据表指定别名
	 * < $oSelect->from(array('别名'=> '表名称'),'字段名,字段名')); >
	 *
	 * < // 通过表数据入口指定表
	 *   $oSelect->from($TablePosts,'字段名,字段名'); >
	 *
	 * < // 通过表数据入口指定表和别名
	 *   $oSelect->from(array('别名'=> $TablePosts),array('别名'=> '字段名','字段名')); >
	 *
	 * < 如果 $Table 参数为空，则通过 $cols 参数指定的字段名前面不会添加数据表名称 >
	 *
	 * @access public
	 * @param array,string,DbTableEnter $Table 表
	 * @param array,string,DbDbExpression $Cols 字段
	 * @return DbRecordSet
	 */
	public function from($Table,$Cols=self::SQL_WILDCARD){}

	/**
	 * 添加要查询的字段
	 *
	 * < $Cols 和 $Table 参数的规则同 from()方法 >
	 * < 如果没有指定 $Table 参数，则假定这些字段属于第一个 FROM 操作添加的表 >
	 * < 但是也可以用“表名称.字段名”的方式来指定字段所属表 >
	 * < 除此以外，还可以通过 $Table 参数批量指定这些字段所属表 >
	 *
	 * @access public
	 * @param  array|string|DbExpression $Cols 字段
	 * @param  array|string|DbTableEnter $Table 表
	 *
	 * @return DbRecordSet
	 */
	public function columns($Cols=self::SQL_WILDCARD,$Table=null){}

	/**
	 * 指定要查询的字段
	 *
	 * < $Cols 和 $Table 参数的规则同 from()方法 >
	 * < 如果没有指定 $Table 参数，则假定这些字段属于第一个 FROM 操作添加的表 >
	 * < 但是也可以用“表名称.字段名”的方式来指定字段所属表 >
	 * < 除此以外，还可以通过 $Table 参数批量指定这些字段所属表 >
	 *
	 * @access public
	 * @param array|string|DbExpression $Cols 字段
	 * @param array|string|DbTableEnter $Table 表
	 * @return DbRecordSet
	 */
	 public function setColumns($Cols=self::SQL_WILDCARD,$Table=null){}

	/**
	 * 添加一个 WHERE 查询条件，与其他 WHERE 条件之间以 AND 布尔运算符连接
	 *
	 * < where()方法的参数格式是可变的，具有下列几种形式：>
	 *
	 * < // 使用字符串做查询条件
	 *   $oSelect->where('id=1')>
	 *
	 * < // 使用 ? 作为参数占位符 >
	 *   $oSelect->where('id=?',$id)>
	 *
	 * < // 使用多个参数占位符
	 *   $oSelect->where('id=? AND level_ix > ?',$id,$level_ix)>
	 *
	 * < // 使用数组提供多个参数占位符的值
	 *   $oSelect->where('id=? AND level_ix > ?',array($id,$level_ix))>
	 *
	 * < // 使用命名参数
	 *   $oSelect->where('id=:id AND level_ix > :level_ix',array(
	 *     'id'=> $id,'level_ix'=> $level_ix
	 *))>
	 *
	 * < // 使用名值对
	 *   $oSelect->where(array('id'=> $id,'level_ix'=> $level_ix)); >
	 *
	 * < 注意：在使用命名参数时，where()的第二个参数必须是一个名值对数组,其中键名是参数名 >
	 *
	 * < 在查询条件中，还可以使用“[]”来指定需要转义的字段名，例如：>
	 *
	 * < $oSelect->where('[id]=1'); >
	 * < $oSelect->where('[posts.id]=1'); >
	 *
	 * < 除了字符串和数组，$Cond 参数还可以是 DbExpression 对象，例如：>
	 *
	 * < $oExpr=new DbExpression('[hits] < AVG([hits])');
	 *   $oSelect->where($oExpr); >
	 *
	 * < 如果没有在字段名中指定表名称或者别名，则假定所有字段都是第一个通过 from()指定的表 >
	 * < 更复杂的查询条件，可以使用 DbCond 对象来构造 >
	 *
	 * @access public
	 * @param string|array|DbExpression|DbCond $Cond 查询条件
	 * @return DbRecordSet
	 */
	public function where($Cond /* args */){}

	/**
	 * 添加一个 WHERE 查询条件，与其他 WHERE 条件之间以 OR 布尔运算符连接
	 *
	 * < 参数规范参考 where()方法 >
	 *
	 * @access public
	 * @param mixed $Cond 条件
	 * @return DbRecordSet
	 */
	public function orWhere($Cond /* args */){}

	/**
	 * 添加一个 JOIN 数据表和字段到查询中
	 *
	 * < $Table 和 $Cols 参数的规则同 from()，$Cond 参数的规则同 where()>
	 *
	 * @access public
	 * @param array|string|DbTableEnter $Table 表名字
	 * @param array|string|DbExpression $Cols 字段
	 * @param array|string|DbExpression|DbCond $Cond 条件
	 * @return DbRecordSet
	 */
	public function join($Table,$Cols=self::SQL_WILDCARD,$Cond /* args */){}

	/**
	 * 添加一个 INNER JOIN 数据表和字段到查询中
	 *
	 * < $Table 和 $Cols 参数的规则同 from()，$Cond 参数的规则同 where()>
	 *
	 * @access public
	 * @param array|string|DbTableEnter $Table 表
	 * @param array|string|DbExpression $Cols 字段
	 * @param array|string|DbExpression|DbCond $Cond 条件
	 * @return DbRecordSet
	 */
	public function joinInner($Table,$Cols=self::SQL_WILDCARD,$Cond){}

	/**
	 * 添加一个 LEFT JOIN 数据表和字段到查询中
	 *
	 * < $Table 和 $Cols 参数的规则同 from()，$Cond 参数的规则同 where()>
	 *
	 * @access public
	 * @param array|string|DbTableEnter $Table 表
	 * @param array|string|DbExpression $Cols 字段
	 * @param array|string|DbExpression|DbCond $Cond 条件
	 * @return DbRecordSet
	 */
	public function joinLeft($Table,$Cols=self::SQL_WILDCARD,$Cond){}

	/**
	 * 添加一个 RIGHT JOIN 数据表和字段到查询中
	 *
	 * < $table 和 $cols 参数的规则同 from()，$cond 参数的规则同 where()>
	 *
	 * @access public
	 * @param array|string|DbTableEnter $Table 表
	 * @param array|string|DbExpression $Cols 字段
	 * @param array|string|DbExpression|DbCond $Cond 条件
	 * @return DbRecordSet
	 */
	public function joinRight($Table,$Cols=self::SQL_WILDCARD,$Cond){}

	/**
	 * 添加一个 FULL OUTER JOIN 数据表和字段到查询中
	 *
	 * < $Table 和 $Cols 参数的规则同 from()，$Cond 参数的规则同 where()>
	 *
	 * @access public
	 * @param array|string|DbTableEnter $Table 表
	 * @param array|string|DbExpression $Cols 字段
	 * @param array|string|DbExpression|DbCond $Cond 条件
	 * @return DbRecordSet
	 */
	public function joinFull($Table,$Cols=self::SQL_WILDCARD,$Cond){}

	/**
	 * 添加一个 CROSS JOIN 数据表和字段到查询中
	 *
	 * < $Table 和 $Cols 参数的规则同 from()>
	 * < CROSS JOIN 交叉连接，对左边每一条记录，都对应右边的每天记录 >
	 *
	 * @access public
	 * @param array|string|DbTableEnter $Table 表
	 * @param array|string|DbExpr $Cols 字段
	 * @return DbRecordSet
	 */
	public function joinCross($Table,$Cols=self::SQL_WILDCARD){}

	/**
	 * 添加一个 NATURAL JOIN 数据表和字段到查询中
	 *
	 * < 自动将相同字段连接起来 >
	 *
	 * @access public
	 * @param array|string|DbTableEnter $Table 表
	 * @param array|string|DbExpression $Cols 字段
	 * @return DbRecordSet
	 */
	public function joinNatural($Table,$Cols=self::SQL_WILDCARD){}

	/**
	 * 添加一个 UNION 查询
	 *
	 * < $Select 可以是一个字符串或一个 DbSelect 对象，或者包含上述两者的数组 >
	 *
	 * @access public
	 * @param array|string|DbRecordSet $Select 查询条件
	 * @param string  $sType 类型
	 * @return DbRecordSet
	 */
	public function union($Select=array(),$sType=self::SQL_UNION){}

	/**
	 * 指定 GROUP BY 子句
	 *
	 * < $Expr 可以是一个字符串或一个 DbExpression 对象，或者包含上述两者的数组 >
	 *
	 * < 如果需要在表达式中使用转义后的字段名，可以采用如下模式：>
	 * < $oSelect->group('SUM([hits])'); >
	 *
	 * < 所有被 [ 和 ] 包括的字段名将自动进行转义。如果有需要，还可以进一步指定字段所属的表或表别名 >
	 * < $oSelect->group('SUM([mytable.hits])'); >
	 *
	 * @access public
	 * @param string|DbExpression|array $Expr 表达式
	 * @return DbRecordSet
	 */
	public function group($Expr){}

	/**
	 * 添加一个 HAVING 条件，与其他 HAVING 条件之间以 AND 布尔运算符连接
	 *
	 * < 参数规范参考 where()方法 >
	 *
	 * @access public
	 * @param string|array|DbExpression|DbCond $Cond 查询条件
	 * @return DbRecordSet
	 */
	public function having($Cond /* args */){}

	/**
	 * 添加一个 HAVING 条件，与其他 HAVING 条件之间以 OR 布尔运算符连接
	 *
	 * < 参数规范参考 where()方法 >
	 *
	 * @access public
	 * @param  string|array|DbExpression|DbCond $Cond 查询条件
	 * @return DbRecordSet
	 */
	public function orHaving($Cond){}

	/**
	 * 添加排序
	 *
	 * < $Expr 可以是字符串或者 DbExpression 对象，例如： >
	 *
	 * < $oSelect->order('title');
	 *   $oSelect->order('users.username DESC');
	 *   $oSelect->order(new DbExpression('SUM(hits)ASC')>
	 *
	 * @access public
	 * @param  string|DbExpression $Expr 表达式
	 * @return DbRecordSet
	 */
	public function order($Expr){}

	/**
	 * 指示仅查询第一个符合条件的记录
	 *
	 * @access public
	 * @return DbRecordSet
	 */
	public function one(){}

	/**
	 * 指示查询所有符合条件的记录
	 *
	 * @access public
	 * @return DbRecordSet
	 */
	public function all(){}

	/**
	 * 限制查询结果总数
	 *
	 * @access public
	 * @param int $nOffset 从结果集的哪个位置开始查询（0 为第一条）
	 * @param int $nCount 只查询多少条数据
	 * @return DbRecordSet
	 */
	public function limit($nOffset=0,$nCount=30){}

	/**
	 * 限定查询结果总数
	 *
	 * @access public
	 * @param int $count
	 * @return DbRecordSet
	 */
	public function top($nCount=30){}

	/**
	 * 是否构造一个 FOR UPDATE 查询
	 *
	 * < 如果查询出记录后马上就要更新并写回数据库，则可以调用 forUpdate()方法来指示这种情况 >
	 * < 此时数据库会尝试对查询出来的记录加锁，避免在数据更新回数据库之前被其他查询改变 >
	 *
	 * @access public
	 * @param boolean $bFlag  是否构造
	 * @return DbRecordSet
	 */
	public function forUpdate($bFlag=true){}

	/**
	 * 统计符合条件的记录数
	 *
	 * < $Field 参数指定用于统计的字段或表达式 >
	 *
	 * @access public
	 * @param string|DbExpression $Field 字段或者表达式
	 * @param string $sAlias 别名
	 * @return DbRecordSet
	 */
	public function count($Field='*',$sAlias='row_count'){}

	/**
	 * 统计平均值
	 *
	 * @access public
	 * @param string|DbExpression $Field  字段
	 * @param string $sAlias 别名
	 * @return DbRecordSet
	 */
	public function avg($Field,$sAlias='avg_value'){}

	/**
	 * 统计最大值
	 *
	 * @access public
	 * @param string|DbExpression $Field 字段
	 * @param string $sAlias 别名
	 * @return DbRecordSet
	 */
	public function max($Field,$sAlias='max_value'){}

	/**
	 * 统计最小值
	 *
	 * @access public
	 * @param string|DbExpress $Field 字段
	 * @param string $sAlias 别名
	 * @return DbRecordSet
	 */
	public function min($Field,$sAlias='min_value'){}

	/**
	 * 统计合计
	 *
	 * @access public
	 * @param string|DbExpress $Field 字段
	 * @param string $sAlias 别名
	 * @return DbRecordSet
	 */
	public function sum($Field,$sAlias='sum_value'){}

	/**
	 * 指示将查询结果封装为特定的 ActiveRecord 对象
	 *
	 * < 通常对于从 ActiveRecord 发起的查询不需要再调用该方法，系统会确保此类查询都返回对象 >
	 * < 但如果是从表数据入口发起的查询，并且希望返回对象，就应该调用这个方法指定一个类名称 >
	 * < 类名称所指定的 ActiveRecord 继承类应该是一个适合返回结果数据结构的对象，否则会导致构造对象失败 >
	 *
	 * @access public
	 * @param string $sClassName 类名
	 * @return DbRecordSet
	 */
	public function asObj($sClassName){}

	/**
	 * 指示将查询结果返回为数组
	 *
	 * < 指示不管查询是由什么来源发起的，都将查询结果以数组方式返回 >
	 *
	 * @access public
	 * @return DbRecordSet
	 */
	public function asArray(){}

	/**
	 * 指示将查询结果作为 DbActiveRecordAssociationColl 集合返回
	 *
	 * @access public
	 * @param boolean $bAsColl
	 * @return DbRecordSet
	 */
	 public function asColl($bAsColl=true){}

	/**
	 * 设置一个或多个字段的映射名，如果 $sMappingTo 为 NULL，则取消对指定字段的映射
	 *
	 * < 映射名是指可以在查询参数中使用映射名作为字段名 >
	 * < DbRecordSet 会负责在生成查询时将映射名转换为实际的字段名 >
	 *
	 * < 例如：>
	 *
	 * < $oSelect->columnMapping('title','post_title')
	 *   ->where(array('post_title'=> $title));
	 *   // 生成的查询条件是 `title`={$title} 而不是 `post_title`={$title} >
	 *
	 * @access public
	 * @param array|string $Name 名字
	 * @param string $sMappingTo 映射名
	 * @return DbRecordSet
	 */
	public function columnMapping($Name,$sMappingTo=NULL){}

	/**
	 * 获得用于构造查询的指定部分内容
	 *
	 * @access public
	 * @param string $sOption 指定部分
	 * @return mixed
	 */
	public function getOption($sOption){}

	/**
	 * 重置整个查询对象或指定部分
	 *
	 * @access public
	 * @param string $sOption 指定部分
	 * @return DbRecordSet
	 */
	public function reset($sOption=null){}

	/**
	 * 获得查询字符串
	 *
	 * @access public
	 * @return string
	 */
	public function makeSql(){}

	/**
	 * 构造 DISTINCT 子句
	 *
	 * @access protected
	 * @return string
	 */
	protected function parseDistinct_(){}

	/**
	 * 构造查询字段子句
	 *
	 * @access protected
	 * @return string
	 */
	protected function parseColumns_(){}

	/**
	 * 构造集合查询字段
	 *
	 * @access protected
	 * @return string
	 */
	protected function parseAggregate_(){}

	/**
	 * 构造 FROM 子句
	 *
	 * @access protected
	 * @return string
	 */
	protected function parseFrom_(){}

	/**
	 * 构造 UNION 查询
	 *
	 * @access protected
	 * @return string
	 */
	protected function parseUnion_(){}

	/**
	 * 构造 WHERE 子句
	 *
	 * @access protected
	 * @return string
	 */
	protected function parseWhere_(){}

	/**
	 * 构造 GROUP 子句
	 *
	 * @access protected
	 * @return string
	 */
	protected function parseGroup_(){}

	/**
	 * 构造 HAVING 子句
	 *
	 * @access protected
	 * @return string
	 */
	protected function parseHaving_(){}

	/**
	 * 构造 ORDER 子句
	 *
	 * @access protected
	 * @return string
	 */
	protected function parseOrder_(){}

	/**
	 * 构造 FOR UPDATE 子句
	 *
	 * @access protected
	 * @return string
	 */
	protected function parseForUpdate_(){}

	/**
	 * 添加一个 JOIN
	 *
	 * @access protected
	 * @param string $sJoinType join类型
	 * @param array|string|DbTableEnter $Name 表名字
	 * @param array|string|DbExpression $Cols 字段
	 * @param array|string|DbExpression|DbCond $Cond 查询条件
	 * @param array $arrCondArgs 查询条件参数
	 * @return DbSelect
	 */
	protected function join_($sJoinType,$Name,$Cols,$Cond=null,$arrCondArgs=null){}

	/**
	 * 添加到内部的数据表->字段名映射数组
	 *
	 * @param string $sTableName 表名字
	 * @param array|string|DbExpression $Cols 字段
	 * @return void
	 */
	protected function addCols_($sTableName,$Cols){}

	/**
	 * 添加查询条件的内容方法
	 *
	 * @param string|array|DbExpression|DbCond $Cond 条件
	 * @param array $arrArgs 参数
	 * @param string $sPartType 类型
	 * @param bool $bBool true=AND,false=OR 参数类型
	 *
	 * @return DbRecodeSet
	 */
	protected function addConditions_($Cond,array $arrArgs,$sPartType,$bBool){}

	 /**
	 * 获得当前表的名称
	 *
	 * @access protected
	 * @return string
	 */
	protected function getCurrentTableName_(){}

	/**
	 * 回调函数，用于分析查询中包含的关联表名称
	 *
	 * @access public
	 * @param string $sTableName 表名字
	 * @return string
	 */
	public function parseTableName_($sTableName){}

	/**
	 * 添加一个集合查询
	 *
	 * @param string $sType 类型
	 * @param string|DbExpression $Field 字段
	 * @param string $sAlias 别名
	 * @return DbRecordSet
	 */
	protected function addAggregate_($sType,$Field,$sAlias){}

	/**
	 * 获得一个唯一的别名
	 *
	 * @access private
	 * @param string|array $Name 名字
	 * @return string
	 */
	private function uniqueAlias_($Name){}

}
