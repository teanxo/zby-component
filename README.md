# zby-component

基于Hyperf框架的自定义组件库

包含功能如下：
1. Token校验
2. 注解拓展
3. 全局异常拦截器
4. Bean拓展(ArrayToBean、BeanToArray)
5. Collection集合拓展
6. Validation验证器拓展
2. 跨域处理
3. Excel处理器
4. 模型(Model)拓展
5. 服务(Service)拓展
6. 命令(Command)拓展

## 1.0.2版本更新
### 已废弃

1. Hyperf\Zby\Annotation\Validation\Email(邮箱验证)

2. Hyperf\Zby\Annotation\Validation\IdCard(身份证验证)

3. Hyperf\Zby\Annotation\Validation\Max(最大值验证)

4. Hyperf\Zby\Annotation\Validation\Min(最小值验证)

5. Hyperf\Zby\Annotation\Validation\Mobile(手机号验证)

6. Hyperf\Zby\Annotation\Validation\Required(必填验证)
7. Hyperf\Zby\Annotation\Validation\RequestBody(实体类验证)
8. Hyperf\Zby\Annotation\Validation\ValidateBody(实体类验证)
9. Hyperf\Zby\Annotation\Validation\RequestParamter(参数验证)
10. Hyperf\Zby\Annotation\Validation\Valid(自定义验证)

7. Hyperf\Zby\Annotation\Validation\Validator(自定义验证)

8. Hyperf\Zby\Aspect\ValidationAspect(验证切面)

9. Hyperf\Zby\Command\GenBeanCommand(生成实体类)
10. Hyperf\Zby\Constants\UserIdFieldConstants(数据库关联字段类)

### 变更

1. Hyperf\Zby\Annotation\OperationLog -> Hyperf\Zby\Annotation\VisitLog
2. Hyperf\Zby\Aspect\LogAspect -> Hyperf\Zby\Aspect\VisitLogAspect