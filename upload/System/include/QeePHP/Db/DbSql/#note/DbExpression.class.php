<?qeephp
class DbExpression {

	/**
	 * 封装的表达式
	 *
	 * @access protected
	 * @var string
	 */
	protected $_sExpr;

	/**
	 * 构造函数
	 *
	 * @access public
	 * @param string $sExpr 表达式
	 * @return void
	 */
	public function __construct($sExpr){}

	/**
	 * 返回表达式的字符串表示
	 *
	 * @access public
	 * @return string
	 */
	public function __toString(){}

	/**
	 * 格式化为字符串
	 *
	 * @access public
	 * @param DbConnect $oConnect 连接句柄
	 * @param string $sTableName 表名字
	 * @param array $arrMapping 映射
	 * @param callback $hCallback 回调
	 *
	 * @return string
	 */
	public function makeSql($oConnect,$sTableName=null,array $arrMapping=null,$hCallback=null){}

}
