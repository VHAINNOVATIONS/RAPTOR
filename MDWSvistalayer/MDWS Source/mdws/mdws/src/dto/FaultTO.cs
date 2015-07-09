using System;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdws.dto
{

    /// <summary>
    /// TBD VAN Refactor to reuse constructor code!
    /// </summary>
    public class FaultTO
    {
        public string type;
        public string message;
        public string stackTrace;
        public string innerType;
        public string innerMessage;
        public string innerStackTrace;
        public string suggestion;

        public FaultTO()
        {
            type = "";
            message = "";
            stackTrace = "";
            suggestion = "";
        }

        public FaultTO(string msg)
        {
            type = "";
            message = MdwsUtils.replaceSpecialXmlChars(msg);
            stackTrace = "";
            suggestion = "";
        }

        public FaultTO(string msg, string suggestion)
        {
            type = "";
            message = MdwsUtils.replaceSpecialXmlChars(msg);
            stackTrace = "";
            this.suggestion = MdwsUtils.replaceSpecialXmlChars(suggestion);
        }

        public FaultTO(Exception e)
        {
            setPropertiesFromException(e);
        }

        public FaultTO(Exception e, String suggestion)
        {
            setPropertiesFromException(e);
            this.suggestion = suggestion;
        }

        void setPropertiesFromException(Exception e)
        {
            // we can put some intelligent MdoException handling code here to pass the more MDO specific
            // error information to the client
            if (e is MdoException)
            {
                message = MdwsUtils.replaceSpecialXmlChars((e as MdoException).ToString());
            }
            else
            {
                message = MdwsUtils.replaceSpecialXmlChars(e.Message);
                // put this in else because MdoException.ToString() will parse this data
                if (e.InnerException != null)
                {
                    innerType = MdwsUtils.replaceSpecialXmlChars(e.InnerException.GetType().ToString());
                    innerMessage = MdwsUtils.replaceSpecialXmlChars(e.InnerException.Message);
                    innerStackTrace = MdwsUtils.replaceSpecialXmlChars(e.InnerException.StackTrace);
                }
            }
            type = MdwsUtils.replaceSpecialXmlChars(e.GetType().ToString());
            stackTrace = MdwsUtils.replaceSpecialXmlChars(e.StackTrace);
            suggestion = "";
        }

    }
}
