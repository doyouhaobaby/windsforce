<?qeephp
class DbCond{

	/**
	 * 定义组
	 *
	 * @const
	 * @var string
	 */
	const BEGIN_GROUP='(';
	const END_GROUP=')';

	/**
	 * 构成查询条件的各个部分
	 *
	 * @var array
	 */
	protected $_arrOptions=array();

	/**
	 * 构造函数
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()}

	/**
	 * 创建一个 DbCond 对象，便于使用连贯接口
	 *
	 * @access public
	 * @static
	 * @return DbCond
	 */
	public static function create(){}

	/**
	 * 直接创建一个 DbCond 对象
	 *
	 * @access public
	 * @static
	 * @param string|array|DbExpression|DbCond $Cond 条件
	 * @param array $arrCondArgs
	 * @param bool $bBool
	 * @return DbCond
	 */
	public static function createByArgs($Cond,array $arrCondArgs=null,$bBool=true){}

	/**
	 * 直接添加一个查询条件
	 *
	 * @accesss public
	 * @param array $arrArgs 参数
	 * @param bool $bBool 值
	 * @return DbCond
	 */
	public function appendDirect(array $arrArgs,$bBool=true){}

	/**
	 * 添加一个新条件，与其他条件之间使用 AND 布尔运算符连接
	 *
	 * @accesss public
	 * @return DbCond
	 */
	public function andCond(){}

	/**
	 * 添加一个新条件，与其他条件之间使用 OR 布尔运算符连接
	 *
	 * @accesss public
	 * @return DbCond
	 */
	public function orCond(){}

	/**
	 * 开始一个条件组，AND
	 *
	 * @accesss public
	 * @return DbCond
	 */
	public function andGroup(){}

	/**
	 * 开始一个条件组，OR
	 *
	 * @accesss public
	 * @return DbCond
	 */
	public function orGroup(){}

	/**
	 * 结束一个条件组
	 *
	 * @accesss public
	 * @return DbCond
	 */
	public function endGroup(){}

	/**
	 * 格式化为字符串
	 *
	 * @accesss public
	 * @param DbConnect $oConnect 连接句柄
	 * @param string $sTableName 表名字
	 * @param array $arrFieldsMapping 字段映射
	 * @param callback $hCallback 回调
	 * @return string
	 */
	public function makeSql($oConnect,$sTableName=null,array $arrFieldsMapping=null,$hCallback=null){}

}
