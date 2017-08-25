using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using System.IO;
using System.Runtime.CompilerServices;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.src.mdo;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaUtils
    {
        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string adjustForNameSearch(string target)
        {
            int lth = target.Length;
            if (lth == 0)
            {
                return "";
            }
            //target = target.ToUpper();
            string rtn = target.Substring(0, lth - 1);
            char c = target[lth - 1];
            int asciiCode = (byte)c - 1;
            c = (char)asciiCode;
            rtn = rtn + c + '~';
            return rtn;
        }

        /// <summary>
        /// </summary>
        /// <param name="target"></param>
        /// <returns></returns>
        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string adjustForNumericSearch(string target)
        {
            Int64 iTarget = Convert.ToInt64(target);
            return Convert.ToString(iTarget - 1);
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string setDirectionParam(string direction) 
	    {
		    if (String.IsNullOrEmpty(direction))
		    {
			    return "1";
		    }
		    if (!String.Equals(direction, "1") && !String.Equals(direction, "-1"))
		    {
			    throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Invalid direction.  Must be 1 or -1.");
		    }
		    return direction;
	    }

        // These 2 methods should be reduced to one where only dfn and arg are required, the other args optional.
        // Problem with doing that now is you can't null an int (nrpts).
        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static MdoQuery buildReportTextRequest(string dfn, string fromDate, string toDate, int nrpts, string arg)
        {
            if (String.IsNullOrEmpty(fromDate) || fromDate.Equals("0"))
            {
                fromDate = DateUtils.MinDate;
            }

            if (String.IsNullOrEmpty(toDate))
            {
                toDate = "0";
            }

            CheckRpcParams(dfn, fromDate, toDate);

            if (String.IsNullOrEmpty(arg))
            {
                throw new NullOrEmptyParamException("routine name");
            }

            VistaQuery vq = new VistaQuery("ORWRP REPORT TEXT");
            vq.addParameter(vq.LITERAL, dfn);
            if (nrpts != 0)
            {
                arg += nrpts.ToString();
            }
            vq.addParameter(vq.LITERAL, arg);
            vq.addParameter(vq.LITERAL, "");
            vq.addParameter(vq.LITERAL, "");
            vq.addParameter(vq.LITERAL, "");
            if (fromDate != "0")
            {
                fromDate = VistaTimestamp.fromUtcString(fromDate);
            }
            vq.addParameter(vq.LITERAL, fromDate);
            if (toDate != "0")
            {
                toDate = VistaTimestamp.fromUtcString(toDate);
            }
            vq.addParameter(vq.LITERAL, toDate);
            return vq;
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static MdoQuery buildReportTextRequest_AllResults(string dfn, string arg)
        {
            CheckRpcParams(dfn);
            if (String.IsNullOrEmpty(arg))
            {
                throw new NullOrEmptyParamException("routine name");
            }
            VistaQuery vq = new VistaQuery("ORWRP REPORT TEXT");
            vq.addParameter(vq.LITERAL, dfn);
            vq.addParameter(vq.LITERAL, arg + '0');
            vq.addParameter(vq.LITERAL, "");
            vq.addParameter(vq.LITERAL, "50000");
            vq.addParameter(vq.LITERAL, "");
            vq.addParameter(vq.LITERAL, "0");
            vq.addParameter(vq.LITERAL, "0");
            return vq;
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string responseOrOk(string response)
        {
            if (response != "")
            {
                return response;
            }
            return "OK";
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string errMsgOrOK(string response)
        {
            string[] flds = StringUtils.split(response, StringUtils.CARET);
            if (flds[0] != "1")
            {
                return flds[1];
            }
            return "OK";
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string errMsgOrZero(string response)
        {
            string[] flds = StringUtils.split(response, StringUtils.CARET);
            if (flds[0] != "0")
            {
                return flds[1];
            }
            return "OK";
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string errMsgOrIen(string response)
        {
            if (response != "" && !StringUtils.isNumeric(response))
            {
                throw new ArgumentException("Non-numeric IEN");
            }
            return response;
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string removeCtlChars(string s)
        {
            if (String.IsNullOrEmpty(s))
            {
                return "";
            }
            StringBuilder result = new StringBuilder();
            int i = 0;
            while (i < s.Length)
            {
                int c = Convert.ToByte(s[i]);
                if (c == 9 || c == 10 || c == 13 || (c > 31 && c < 127))
                {
                    result.Append(s[i]);
                }
                i++;
            }
            return result.ToString();
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string getVistaName(string s)
        {
            if (!PersonName.isValid(s))
            {
                return "";
            }
            string[] flds = StringUtils.split(s, StringUtils.COMMA);
            if (flds.Length != 2)
            {
                return "";
            }
            string result = flds[0] + ',' + flds[1];
            return result.ToUpper();
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string getVistaGender(string s)
        {
            if (StringUtils.isEmpty(s))
            {
                return "";
            }
            string result = s.Substring(0, 1).ToUpper();
            if (result != "M" && result != "F")
            {
                return "";
            }
            return result;
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string getVisitString(Encounter encounter)
        {
            return encounter.LocationId + ';' +
                VistaTimestamp.fromUtcString(encounter.Timestamp) + ';' +
                encounter.Type;
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static void CheckVisitString(string s)
        {
            if (String.IsNullOrEmpty(s))
            {
                throw new NullOrEmptyParamException("Visit String");
            }
            string[] pieces = s.Split(new char[] { ';' });
            if (pieces.Length != 3)
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Invalid visit string (need 3 semi-colon delimited pieces): " + s);
            }
            if (!isWellFormedIen(pieces[0]))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Invalid visit string (invalid location IEN): " + s);
            }            
            if (!VistaTimestamp.isValid(pieces[1]))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Invalid visit string (invalid VistA timestamp): " + s);
            }
            if (pieces[2] != "A" && pieces[2] != "H" && pieces[2] != "E")
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Invalid visit string (type must be 'A', 'H' or 'E'): " + s);
            }
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static StringDictionary toStringDictionary(string[] response)
        {
            if (response == null || response.Length == 0)
            {
                return null;
            }
            StringDictionary result = new StringDictionary();
            for (int i = 0; i < response.Length; i++)
            {
                string[] flds = StringUtils.split(response[i], StringUtils.CARET);
                result.Add(flds[0], flds[1]);
            }
            return result;
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string getVariableValue(AbstractConnection cxn, string arg)
        {
            MdoQuery request = buildGetVariableValueRequest(arg);
            string response = "";
            try
            {
                response = (string)cxn.query(request);
                return response;
            }
            catch (Exception exc)
            {
                throw new MdoException(request, response, exc);
            }
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static MdoQuery buildGetVariableValueRequest(string arg)
        {
            if (String.IsNullOrEmpty(arg))
            {
                throw new NullOrEmptyParamException("arg");
            }
            VistaQuery vq = new VistaQuery("XWB GET VARIABLE VALUE");
            vq.addParameter(vq.REFERENCE, arg);
            return vq;
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string buildFromToDateScreenParam(string fromDate, string toDate, int node, int pieceNum)
        {
            if (fromDate == "")
            {
                return "";
            }
            DateUtils.CheckDateRange(fromDate, toDate);

            // Need test for valid dates.
            fromDate = VistaTimestamp.fromUtcFromDate(fromDate);
            toDate = VistaTimestamp.fromUtcString(toDate);
            return "S FD=" + fromDate + ",TD=" + toDate + ",CDT=$P(^(" + node + "),U," + pieceNum + ") I CDT>=FD,CDT<TD";
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string encrypt(string inString)
        {
            const int MAXKEY = 19;
            String[] cipherPad =
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
            int identifierIndex = 0;
            do
            {
                identifierIndex = r.Next(MAXKEY);
            } while (associatorIndex == identifierIndex);

            String xlatedString = "";
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

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static AbstractCredentials getAdministrativeCredentials(Site site)
        {
            AbstractCredentials credentials = new VistaCredentials();
            credentials.LocalUid = VistaAccount.getAdminLocalUid(site.Id);
            credentials.FederatedUid = "123456789";
            credentials.SubjectName = "DEPARTMENT OF DEFENSE,USER";
            credentials.SubjectPhone = "";
            credentials.AuthenticationSource = site.getDataSourceByModality("HIS");
            credentials.AuthenticationToken = site.Id + '_' + credentials.LocalUid;
            return credentials;
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static StringDictionary reverseKeyValue(StringDictionary d)
        {
            StringDictionary result = new StringDictionary();
            foreach (string key in d.Keys)
            {
                result.Add(d[key], key);
            }
            return result;
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static bool isWellFormedDuz(string duz)
        {
            return !String.IsNullOrEmpty(duz) && !duz.Equals("0") && StringUtils.isNumeric(duz);
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static bool isWellFormedIen(string ien)
        {
            return !String.IsNullOrEmpty(ien) && !ien.Equals("0") &&
                (StringUtils.isNumeric(ien) || StringUtils.isDecimal(ien));
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static void CheckRpcParams(string ien, string fromDate = null, string toDate = null)
        {
            if (!isWellFormedIen(ien))
            {
                throw new InvalidlyFormedRecordIdException(ien);
            }
            
            //4/14/2011 David Parshan
            //Added toDate check, as there is the ability to have a valid 
            //fromDate with a default "" toDate
            if (!String.IsNullOrEmpty(fromDate) && fromDate != "0" && toDate != "" && toDate != "0" && toDate != "-1")
            {
                DateUtils.CheckDateRange(fromDate, toDate);
            }
            else
            {
                if (!String.IsNullOrEmpty(fromDate) && fromDate != "0")
                {
                    if (!DateUtils.isWellFormedUtcDateTime(fromDate))
                    {
                        throw new MdoException(MdoExceptionCode.ARGUMENT_DATE_FORMAT, "Invalid 'from' date: " + fromDate);
                    }
                }
            }
        }
    }
}
