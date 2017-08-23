VEFBRPC ; NST - VistA RPC wrapper ; 09/01/2015 12:00PM
 ;;
 Q
 ;
HELLOWORLD()
 Q "HELLO VEFB!"
GETJOB()
 Q $J
RPCEXECUTE(TMP,DT) ;
 ;
 ; Execute an RPC based on paramaters provided in TMP reference global
 ;
 ; Input parameter
 ; ================
 ;
 ; TMP is a reference to a global with nodes. e.g.,  ^TMP($J)
 ;
 ;   ,"name")      NAME (#8994, .01)
 ;   ,"version")   VERSION (#8994, .09)
 ;   ,"use") = L|R
 ;   ,"input",n,"type")   PARAMETER TYPE (#8994.02, #02)
 ;   ,"input",n,"value")  input parameter value
 ;      e.g.
 ;      ,"input",n,"type")="LITERAL"
 ;      ,"input",n,"value")="abc"
 ;
 ;      ,"input",n,"type")="REFERENCE"
 ;      ,"input",n,"value")="^ABC"
 ;
 ;      ,"input",n,"type")="LIST"
 ;      ,"input",n,"value",m1)="list1"
 ;      ,"input",n,"value",m2,k1)="list21"
 ;      ,"input",n,"value",m2,k2)="list22"
 ;         
 ;          where m1, m2, k1, k2 are numbers or strings
 ;     
 ; Output value
 ; ==============
 ; The RPC output is in  @TMP@("result")
 ;  e.g., ,"result","type")="SINGLE VALUE"
 ;                  "value")="Hello World!"
 ;                
 ; Return {"success": result, "message" : message }
 ;    result 1 - success
 ;           0 - error
 ;
 ;k (TMP,DT)
 N rpc,pRpc,tArgs,tCnt,tI,tOut,tResult,X
 N XWBAPVER,DUZ,DT
 ;
 S U=$G(U,"^")  ; set default to "^"
 ;
 S pRpc("name")=$G(@TMP@("name"))
 S:pRpc("name")["ORWDX SEND" ^TMP($J,"input",5,"value")=""
 Q:pRpc("name")="" $$error(-1,"RPC name is missing")
 ;
 S rpc("ien")=$O(^XWB(8994,"B",pRpc("name"),""))
 Q:'rpc("ien") $$error(-2,"Undefined RPC ["_pRpc("name")_"]")
 ;
 S XWBAPVER=$G(@TMP@("version"))
 S pRpc("use")=$G(@TMP@("use"))
 S pRpc("context")=$G(@TMP@("context"))
 S pRpc("duz")=$G(@TMP@("duz"))
 S pRpc("division")=$G(@TMP@("division"))
 ; Set DUZ
 S DUZ=pRpc("duz")
 S:'$D(DUZ(2)) DUZ(2)=pRpc("division")
 S:DUZ DUZ(0)=$P(^VA(200,DUZ,0),U,4)
 S DT=$G(@TMP@("dt"))
 ;
 S X=$G(^XWB(8994,rpc("ien"),0)) ;e.g., XWB EGCHO STRING^ECHO1^XWBZ1^1^R
 S rpc("routineTag")=$P(X,"^",2)
 S rpc("routineName")=$P(X,"^",3)
 Q:rpc("routineName") $$error(-4,"Undefined routine name for RPC ["_pRpc("name")_"]")
 ;
 ; 1=SINGLE VALUE; 2=ARRAY; 3=WORD PROCESSING; 4=GLOBAL ARRAY; 5=GLOBAL INSTANCE
 S rpc("resultType")=$P(X,"^",4)
 S rpc("resultWrapOn")=$P(X,"^",8)
 ;
 ; is the RPC available
 D CKRPC^XWBLIB(.tOut,pRpc("name"),pRpc("use"),XWBAPVER)
 Q:'tOut $$error(-3,"RPC ["_pRpc("name")_"] cannot be run at this time.")
 ;
 S X=$$CHKPRMIT(pRpc("name"),pRpc("duz"),pRpc("context"))
 Q:X'="" $$error(-4,"RPC ["_pRpc("name")_"] is not allowed to be run: "_X)
 ;
 S X=$$buildArguments(.tArgs,rpc("ien"),TMP)  ; build RPC arguments list - tArgs
 Q:X<0 $$error($P(X,U),$P(X,U,2)) ; error building arguments list
 ;
 ; now, prepare the arguments for the final call
 ; it is outside of the $$buildArgumets so we can newed the individual parameters
 S (tI,tCnt)=""
 F  S tI=$O(tArgs(tI)) Q:tI=""  F  S tCnt=$O(tArgs(tI,tCnt)) Q:tCnt=""  N @("tA"_tI) X tArgs(tI,tCnt)  ; set/merge actions
 ;
 S X="D "_rpc("routineTag")_"^"_rpc("routineName")_"(.tResult"_$S(tArgs="":"",1:","_tArgs)_")"
 S DIC(0)="" ; JAM 2014/9/5 - some obscure problem with LAYGO^XUA4A7
 X X  ; execute the routine
 M @TMP@("result","value")=tResult
 S @TMP@("result","type")=$$EXTERNAL^DILFD(8994,.04,,rpc("resultType"))
 S trash=$$success()
 Q "OK"
 ;
 ;
isInputRequired(pIEN,pSeqIEN) ; is input RPC parameter is required
 ; pIEN - RPC IEN in file #8994
 ; pSeqIEN - Input parameter IEN in multiple file #8994.02
 ;
 Q $P(^XWB(8994,pIEN,2,pSeqIEN,0),U,4)=1
 ;
buildArguments(out,pIEN,TMP) ;Build RPC argument list
 ;
 ; Return values
 ; =============
 ; Success 1
 ; Error   -n^error message
 ;
 ; out array with arguments
 N tCnt,tError,tIEN,tI,tII,tRequired,tParam,tIndexSeq,X
 ;
 S tI=0
 S tII=""
 S tCnt=0
 ;
 K out
 S out=""
 S tError=0
 S tIndexSeq=$D(^XWB(8994,pIEN,2,"PARAMSEQ"))  ; is the cross-reference defined
 S tParam=$S(tIndexSeq:"^XWB(8994,pIEN,2,""PARAMSEQ"")",1:"^XWB(8994,pIEN,2)")
 ;
 S count=0
 F  S tII=$O(@TMP@("input",tII)) Q:('tII)!(tError)  D
 . S count=count+1
 . S tIEN=tII  ; get the IEN of the input parameter
 . I '$D(@TMP@("input",tII,"value")) S out=out_"," Q
 . I $D(@TMP@("input",tII,"value"))=1 D  Q
 . . S out=out_"tA"_tII_","   ; add the argument
 . . I $$UP^XLFSTR($G(@TMP@("input",tII,"type")))="REFERENCE" D
 . . . S tCnt=tCnt+1,out(tII,tCnt)="S tA"_tII_"=@@TMP@(""input"","_tII_",""value"")"  ; set it
 . . . Q
 . . E  S tCnt=tCnt+1,out(tII,tCnt)="S tA"_tII_"=@TMP@(""input"","_tII_",""value"")"  ; set it as action for later
 . . Q
 . ; list/array
 . S out=out_".tA"_tII_","
 . S tCnt=tCnt+1,out(tII,tCnt)="M tA"_tII_"=@TMP@(""input"","_tII_",""value"")"  ; merge it
 . Q
 ;
 Q:tError tError
 S out=$E(out,1,$L(out)-1)
 Q 1
 ;
formatResult(code,message) ; return JSON formatted result
 S ^TMP($J,"RPCEXECUTE","result")=code_U_message
 Q "OK"
 ;Q "{""success"": "_code_", ""message"": """_$S($TR(message," ","")="":"",1:message)_"""}"
 ;
error(code,message) ;
 Q $$formatResult(0,code_" "_message)
 ;
success(code,message) ;
 Q $$formatResult(1,$G(code)_" "_$G(message))
 ;
 ; Is RPC pertmited to run in a context?
CHKPRMIT(pRPCName,pUser,pContext) ;checks to see if remote procedure is permited to run
 ;Input:  pRPCName - Remote procedure to check
 ;        pUser    - User
 ;        pContext - RPC Context
 Q:$$KCHK^XUSRB("XUPROGMODE",pUser) ""  ; User has programmer key
 N result,X
 N XQMES
 S U=$G(U,"^")
 S result="" ;Return XWBSEC="" if OK to run RPC
 ;
 ;In the beginning, when no DUZ is defined and no context exist,
 ;setup default signon context
 S:'$G(pUser) pUser=0,pContext="XUS SIGNON"   ;set up default context
 ;
 ;These RPC's are allowed in any context, so we can just quit
 S X="^XWB IM HERE^XWB CREATE CONTEXT^XWB RPC LIST^XWB IS RPC AVAILABLE^XUS GET USER INFO^XUS GET TOKEN^XUS SET VISITOR^"
 S X=X_"XUS KAAJEE GET USER INFO^XUS KAAJEE LOGOUT^"  ; VistALink RPC's that are always allowed.
 I X[(U_pRPCName_U) Q result
 ;
 ;
 ;If in Signon context, only allow XUS and XWB rpc's
 I $G(pContext)="XUS SIGNON","^XUS^XWB^"'[(U_$E(pRPCName,1,3)_U) Q "Application context has not been created!"
 ;XQCS allows all users access to the XUS SIGNON context.
 ;Also to any context in the XUCOMMAND menu.
 ;
 I $G(pContext)="" Q "Application context has not been created!"
 ;
 S X=$$CHK^XQCS(pUser,pContext,pRPCName)         ;do the check
 S:'X result=X
 Q result
 ;
arrayToJSON(name)
 n subscripts
 i '$d(@name) QUIT "[]"
 QUIT $$walkArray("",name)
 ;
walkArray(json,name,subscripts)
 ;
 n allNumeric,arrComma,brace,comma,count,cr,dd,i,no,numsub,dblquot,quot
 n ref,sub,subNo,subscripts1,type,valquot,value,xref,zobj
 ;
 s cr=$c(13,10),comma=","
 s (dblquot,valquot)=""""
 s dd=$d(@name)
 i dd=1!(dd=11) d  i dd=1 QUIT json
 . s value=@name
 . i value'[">" q
 . s json=$$walkArray(json,value,.subscripts)
 s ref=name_"("
 s no=$o(subscripts(""),-1)
 i no>0 f i=1:1:no d
 . s quot=""""
 . i subscripts(i)?."-"1N.N s quot=""
 . s ref=ref_quot_subscripts(i)_quot_","
 s ref=ref_"sub)"
 s sub="",numsub=0,subNo=0,count=0
 s allNumeric=1
 f  s sub=$o(@ref) q:sub=""  d  q:'allNumeric
 . i sub'?1N.N s allNumeric=0
 . s count=count+1
 . i sub'=count s allNumeric=0
 ;i allNumeric,count=1 s allNumeric=0
 i allNumeric d
 . s json=json_"["
 e  d
 . s json=json_"{"
 s sub=""
 f  s sub=$o(@ref) q:sub=""  d
 . s subscripts(no+1)=sub
 . s subNo=subNo+1
 . s dd=$d(@ref)
 . i dd=1 d
 . . s value=@ref
 . . i 'allNumeric d
 . . . s json=json_""""_sub_""":"
 . . s type="literal"
 . . i $$numeric(value) s type="numeric"
 . . ;i value?1N.N s type="numeric"
 . . ;i value?1"-"1N.N s type="numeric"
 . . ;i value?1N.N1"."1N.N s type="numeric"
 . . ;i value?1"-"1N.N1"."1N.N s type="numeric"
 . . i value="true"!(value="false") s type="boolean"
 . . i $e(value,1)="{",$e(value,$l(value))="}" s type="variable"
 . . i $e(value,1,4)="<?= ",$e(value,$l(value)-2,$l(value))=" ?>" d
 . . . s type="variable"
 . . . s value=$e(value,5,$l(value)-3)
 . . i type="literal" s value=valquot_value_valquot
 . . d
 . . . s json=json_value_","
 . k subscripts1
 . m subscripts1=subscripts
 . i dd>9 d
 . . i sub?1N.N,allNumeric d
 . . . i subNo=1 d
 . . . . s numsub=1
 . . . . s json=$e(json,1,$l(json)-1)
 . . . . s json=json_"["
 . . e  d
 . . . s json=json_""""_sub_""":"
 . . s json=$$walkArray(json,name,.subscripts1)
 . . d
 . . . s json=json_","
 ;
 s json=$e(json,1,$l(json)-1)
 i allNumeric d
 . s json=json_"]"
 e  d
 . s json=json_"}"
 QUIT json ; exit!
 ;
numeric(value)
 i $e(value,1,9)="function(" QUIT 1
 i value?1"0."1N.N QUIT 1
 i $e(value,1)=0,$l(value)>1 QUIT 0
 i $e(value,1,2)="-0",$l(value)>2,$e(value,1,3)'="-0." QUIT 0
 i value?1N.N QUIT 1
 i value?1"-"1N.N QUIT 1
 i value?1N.N1"."1N.N QUIT 1
 i value?1"-"1N.N1"."1N.N QUIT 1
 i value?1"."1N.N QUIT 1
 i value?1"-."1N.N QUIT 1
 QUIT 0
 ;
login(accessCode,verifyCode)
 ;
 ;d trace("login: ac="_accessCode_"; vc="_verifyCode)
 k (accessCode,verifyCode)
 n %,accver,DILOCKTM,displayPersonName,DISYS,%DT,DT,DTIME,DUZ,%H
 n checkRes,%I,I,IO,IOF,IOM,ION,IOS,IOSL,IOST,IOT,J,ok,personDuz,personName
 n POP,results,supervisor,termReason,U,user,V4WVCC,V4WCVMSG
 n X,XOPT,XPARSYS,XQVOL,XQXFLG,XUCI,XUDEV,XUENV,XUEOFF,XUEON
 n XUF,XUFAC,XUIOP,XUVOL,XWBSTATE,XWBTIME,Y
 ;
 s accessCode=$g(accessCode) i accessCode="" q "Missing Access Code"
 s verifyCode=$g(verifyCode) i verifyCode="" q "Missing Verify Code"
 ;
 k results
 s U="^" d NOW^%DTC s DT=X
 s (IO,IO(0),IOF,IOM,ION,IOS,IOSL,IOST,IOT)="",POP=0
 s accver=accessCode_";"_verifyCode
 s accver=$$ENCRYP^XUSRB1(accver)
 d SETUP^XUSRB()
 d VALIDAV^XUSRB(.user,accver)
 s personDuz=user(0)
 ;
 ;KBAZ/ZAG - add logic to check if verify code needs to be changed.
 ;0 = VC does not need to be changed
 ;1 = VC needs to be changed
 s V4WVCC=$g(user(2))
 s V4WCVMSG=$g(user(3)) ;sign in message
 ;
 s termReason=""
 i 'personDuz,$G(DUZ) s termReason=": "_$$GET1^DIQ(200,DUZ_",",9.4) ;Termination reason
 i 'personDuz QUIT user(3)_termReason
 ;
 s personName=$p(^VA(200,personDuz,0),"^")
 s displayPersonName=$p(personName,",",2)_" "_$p(personName,",")
 s results("DT")=DT
 s results("DUZ")=personDuz
 s results("username")=personName
 s results("displayName")=displayPersonName
 s results("greeting")=$g(user(7))
 k ^CacheTempEWD($j)
 m ^CacheTempEWD($j)=results
 ;k ^rob("login") m ^rob("login")=results
 QUIT ""