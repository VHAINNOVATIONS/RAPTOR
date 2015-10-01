using System;
using System.Collections.Generic;
using System.Collections;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.utils;
using gov.va.medora.mdo.src.mdo;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaQuery : MdoQuery
    {
        public String getRpcRequestString()
        {
            return this.buildMessage_NatBroker();
        }

        public String RpcResponseString;

        string rpcName;
        ArrayList parameters = new ArrayList();

        internal int LITERAL = 1;
        internal int REFERENCE = 2;
        internal int LIST = 3;
        internal int WORDPROC = 4;

        // These are only valid if the VistA system is NOT backward compatible and the 
        // Delphi RPC Broker is hardwired on creation to set backward compatibility to
        // true.  Therefore, this is not something to worry about until there's a change
        // in the broker.pre
        //internal int GLOBAL = 5;
        //internal int EMPTY = 6;
        //internal int STREAM = 7;
        
        public VistaQuery() {}

        public VistaQuery(string rpcName)
        {
    	    RpcName = rpcName;
        }

        public override string RpcName
        {
            get { return rpcName; }
            set { rpcName = value; }
        }

        public override ArrayList Parameters
        {
            get { return parameters; }
            set { parameters = value; }
        }

        public void addParameter(int type, string value)
        {
            Parameter vp = new Parameter(type,value);
            parameters.Add(vp);
        }

        public void addParameter(int type, string value, string text)
        {
            Parameter vp = new Parameter(type,value,text);
            parameters.Add(vp);
        }

	    public void addParameter(int type, DictionaryHashList lst)
	    {
		    Parameter vp = new Parameter(type,lst);
		    parameters.Add(vp);
	    }

        public void addEncryptedParameter(int type, string value)
        {
            Parameter vp = new Parameter(type, value, true);
            parameters.Add(vp);
        }

        private string buildApi(string rpcName, string parameters, string fText)
        {
            string sParams = StringUtils.strPack(parameters, 5);
            return StringUtils.strPack(fText + rpcName + '^' + sParams, 5);
        }

        public override string buildMessage()
        {
            //return buildMessage_DirectBroker();
            return buildMessage_NatBroker();
        }

        // Use this builder for the old broker...
        public string buildMessage_DirectBroker()
        {
            const string PREFIX = "{XWB}";
            const string HDR = "007XWB;;;;";

            string sParams = "";
		    DictionaryHashList lst = null;
            string text = null;
		    for (int i=0; i<parameters.Count; i++)
            {
			    Parameter vp = (Parameter)parameters[i];
                int pType = vp.Type;
                if (pType == LITERAL)
                {
                    sParams += StringUtils.strPack('0' + vp.Value,3);
                }
                else if (pType == REFERENCE)
                {
                    sParams += StringUtils.strPack('1' + vp.Value,3);
                }
                else if (pType == LIST)
                {
                    sParams += StringUtils.strPack('2' + vp.Value,3);
				    lst = vp.List;
                }
                else if (pType == WORDPROC)
                {
                    sParams += StringUtils.strPack('2' + vp.Value,3);
                    text = vp.Text;
                }
            }
            string msg = "";
		    if (lst == null && text == null)
		    {
			    msg = StringUtils.strPack(HDR + buildApi(rpcName,sParams,"0"),5);
		    }
		    else if (lst != null)
		    {
			    msg = StringUtils.strPack(HDR + buildApi(rpcName,sParams,"1"),5);
                for (int j = 0; j < lst.Count; j++)
                {
                    DictionaryEntry entry = lst[j];
                    string key = (string)entry.Key;
                    string value = (string)entry.Value;
                    if (value == null || value == "")
                    {
                        value = "\u0001";
                    }
                    msg += StringUtils.strPack(key, 3) + StringUtils.strPack(value, 3);
                }
			    msg += "000";
		    }
            else
            {
                msg = StringUtils.strPack(HDR + buildApi(rpcName,sParams,"1"),5);
                string[] lines = StringUtils.split(text,StringUtils.CRLF);
                for (int i=0; i<lines.Length; i++)
                {
                    msg += StringUtils.strPack(i.ToString(),3) +
                            StringUtils.strPack(lines[i] + StringUtils.CRLF,3);
                }
                msg += "000";
            }
            msg = getVersion() + msg;
		    msg = PREFIX + StringUtils.strPack(msg,5);
		    return msg;
        }

        internal string buildMessage_NatBroker()
        {
            const string PREFIX = "[XWB]";
            const int COUNT_WIDTH = 3;
            const string RPC_VERSION = "1.108";

            string sParams = "5";           // Don't ask my why - from the broker code

            for (int i = 0; i < parameters.Count; i++)
            {
                Parameter vp = (Parameter)parameters[i];
                int pType = vp.Type;
                if (pType == LITERAL)
                {
                    sParams += '0' + StringUtils.LPack(vp.Value, COUNT_WIDTH) + 'f';
                }
                else if (pType == REFERENCE)
                {
                    sParams += '1' + StringUtils.LPack(vp.Value, COUNT_WIDTH) + 'f';
                }
                //else if (pType == EMPTY)
                //{
                //    sParams += "4f";
                //}
                else if (pType == LIST)
                {
                    sParams += '2' + list2string(vp.List);
                }
                //else if (pType == GLOBAL)
                //{
                //    sParams += '3' + list2string(vp.List);
                //}
                //else if (pType == STREAM)
                //{
                //    sParams += '5' + StringUtils.LPack(vp.Value, COUNT_WIDTH) + 'f';
                //}
            }
            string msg = "";

            // More inscrutable ugliness from the Delphi broker
            if (sParams == "5")
            {
                sParams += "4f";
            }

            msg = PREFIX + "11" + Convert.ToString(COUNT_WIDTH) + "02" + StringUtils.SPack(RPC_VERSION) +
                StringUtils.SPack(rpcName) + sParams + '\x0004';
            return msg;
        }

        internal string list2string(DictionaryHashList lst)
        {
            if (lst == null || lst.Count == 0)
            {
                return StringUtils.LPack("", 3) + 'f';
            }
            string result = "";
            for (int i = 0; i < lst.Count; i++)
            {
                DictionaryEntry entry = lst[i];
                string key = (string)entry.Key;
                string value = (string)entry.Value;
                if (String.IsNullOrEmpty(value))
                {
                    value = "\u0001";
                }
                result += StringUtils.LPack(key, 3) + StringUtils.LPack(value, 3) + 't';
            }
            result = result.Substring(0, result.Length - 1) + 'f';
            return result;
        }

        private string getVersion()
        {
            return "|" + Convert.ToChar(1) + "1";
        }

        public class Parameter
        {
            bool encrypted = false;
            int myType;
            string myValue;
            string myText;
            string original;
		    DictionaryHashList myList;

            public Parameter()
            {
                Type = -1;
                Value = "";
            }

            public Parameter(int type, string value, bool encrypted = false)
            {
                Type = type;
                Value = value;

                this.encrypted = encrypted;
                if (encrypted)
                {
                    Value = encrypt(value);
                    original = value;
                }
            }

            public Parameter(int type, string value, string text)
            {
                Type = type;
                Value = value;
                Text = text;
            }

		    public Parameter(int type, DictionaryHashList lst)
		    {
			    Type = type;
			    Value = ".x";
			    List = lst;
		    }

            public int Type
            {
                get { return myType; }
                set { myType = value; }
            }

            public string Value
            {
                get { return myValue; }
                set { myValue = value; }
            }

            public string Text
            {
                get { return myText; }
                set { myText = value; }
            }

            public string Original
            {
                get { return original; }
            }

            public bool Encrypted
            {
                get { return encrypted; }
            }

            public DictionaryHashList List
            {
                get { return myList; }
                set { myList = value; }
            }

            public static string encrypt(string inString)
            {
                const int MAXKEY = 19;
                string[] cipherPad =
                {
                    "wkEo-ZJt!dG)49K{nX1BS$vH<&:Myf*>Ae0jQW=;|#PsO`\'%+rmb[gpqN,l6/hFC@DcUa ]z~R}\"V\\iIxu?872.(TYL5_3",
                    "rKv`R;M/9BqAF%&tSs#Vh)dO1DZP> *fX\'u[.4lY=-mg_ci802N7LTG<]!CWo:3?{+,5Q}(@jaExn$~p\\IyHwzU\"|k6Jeb",
                    "\\pV(ZJk\"WQmCn!Y,y@1d+~8s?[lNMxgHEt=uw|X:qSLjAI*}6zoF{T3#;ca)/h5%`P4$r]G\'9e2if_>UDKb7<v0&- RBO.",
                    "depjt3g4W)qD0V~NJar\\B \"?OYhcu[<Ms%Z`RIL_6:]AX-zG.#}$@vk7/5x&*m;(yb2Fn+l\'PwUof1K{9,|EQi>H=CT8S!",
                    "NZW:1}K$byP;jk)7\'`x90B|cq@iSsEnu,(l-hf.&Y_?J#R]+voQXU8mrV[!p4tg~OMez CAaGFD6H53%L/dT2<*>\"{\\wI=",
                    "vCiJ<oZ9|phXVNn)m K`t/SI%]A5qOWe\\&?;jT~M!fz1l>[D_0xR32c*4.P\"G{r7}E8wUgyudF+6-:B=$(sY,LkbHa#\'@Q",
                    "hvMX,\'4Ty;[a8/{6l~F_V\"}qLI\\!@x(D7bRmUH]W15J%N0BYPkrs&9:$)Zj>u|zwQ=ieC-oGA.#?tfdcO3gp`S+En K2*<",
                    "jd!W5[];4\'<C$/&x|rZ(k{>?ghBzIFN}fAK\"#`p_TqtD*1E37XGVs@0nmSe+Y6Qyo-aUu%i8c=H2vJ\\) R:MLb.9,wlO~P",
                    "2ThtjEM+!=xXb)7,ZV{*ci3\"8@_l-HS69L>]\\AUF/Q%:qD?1~m(yvO0e\'<#o$p4dnIzKP|`NrkaGg.ufCRB[; sJYwW}5&",
                    "vB\\5/zl-9y:Pj|=(R\'7QJI *&CTX\"p0]_3.idcuOefVU#omwNZ`$Fs?L+1Sk<,b)hM4A6[Y%aDrg@~KqEW8t>H};n!2xG{",
                    "sFz0Bo@_HfnK>LR}qWXV+D6`Y28=4Cm~G/7-5A\\b9!a#rP.l&M$hc3ijQk;),TvUd<[:I\"u1\'NZSOw]*gxtE{eJp|y (?%",
                    "M@,D}|LJyGO8`$*ZqH .j>c~h<d=fimszv[#-53F!+a;NC\'6T91IV?(0x&/{B)w\"]Q\\YUWprk4:ol%g2nE7teRKbAPuS_X",
                    ".mjY#_0*H<B=Q+FML6]s;r2:e8R}[ic&KA 1w{)vV5d,$u\"~xD/Pg?IyfthO@CzWp%!`N4Z\'3-(o|J9XUE7k\\TlqSb>anG",
                    "xVa1\']_GU<X`|\\NgM?LS9{\"jT%s$}y[nvtlefB2RKJW~(/cIDCPow4,>#zm+:5b@06O3Ap8=*7ZFY!H-uEQk; .q)i&rhd",
                    "I]Jz7AG@QX.\"%3Lq>METUo{Pp_ |a6<0dYVSv8:b)~W9NK`(r\'4fs&wim\\kReC2hg=HOj$1B*/nxt,;c#y+![?lFuZ-5D}",
                    "Rr(Ge6F Hx>q$m&C%M~Tn,:\"o\'tX/*yP.{lZ!YkiVhuw_<KE5a[;}W0gjsz3]@7cI2\\QN?f#4p|vb1OUBD9)=-LJA+d`S8",
                    "I~k>y|m};d)-7DZ\"Fe/Y<B:xwojR,Vh]O0Sc[`$sg8GXE!1&Qrzp._W%TNK(=J 3i*2abuHA4C\'?Mv\\Pq{n#56LftUl@9+",
                    "~A*>9 WidFN,1KsmwQ)GJM{I4:C%}#Ep(?HB/r;t.&U8o|l[\'Lg\"2hRDyZ5`nbf]qjc0!zS-TkYO<_=76a\\X@$Pe3+xVvu",
                    "yYgjf\"5VdHc#uA,W1i+v\'6|@pr{n;DJ!8(btPGaQM.LT3oe?NB/&9>Z`-}02*%x<7lsqz4OS ~E$\\R]KI[:UwC_=h)kXmF",
                    "5:iar.{YU7mBZR@-K|2 \"+~`M%8sq4JhPo<_X\\Sg3WC;Tuxz,fvEQ1p9=w}FAI&j/keD0c?)LN6OHV]lGy\'$*>nd[(tb!#"
                };
                Random r = new Random();
                int associatorIndex = r.Next(MAXKEY);
                int identifierIndex = r.Next(MAXKEY);
                while (associatorIndex == identifierIndex)
                {
                    identifierIndex = r.Next(MAXKEY);
                }
                string xlatedString = "";
                for (int i = 0; i < inString.Length; i++)
                {
                    char inChar = inString[i];
                    int pos = cipherPad[associatorIndex].IndexOf(inChar);
                    if (pos == -1)
                    {
                        xlatedString += inChar;
                    }
                    else
                    {
                        xlatedString += cipherPad[identifierIndex][pos];
                    }
                }
                return (char)(associatorIndex + 32) +
                    xlatedString +
                    (char)(identifierIndex + 32);
            }
        }
    }
}
