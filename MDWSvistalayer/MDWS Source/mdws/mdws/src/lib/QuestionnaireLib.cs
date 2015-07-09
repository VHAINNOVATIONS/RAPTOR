using System;
using System.Data;
using System.Configuration;
using System.Web;
using System.Web.Security;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Web.UI.WebControls.WebParts;
using System.Web.UI.HtmlControls;
using gov.va.medora.mdo;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws
{
    public class QuestionnaireLib
    {
        MySession mySession;

        public QuestionnaireLib(MySession theSession)
        {
            mySession = theSession;
        }

        public QuestionnaireSetTO getQuestionnaireSet(string name)
        {
            QuestionnaireSetTO result = new QuestionnaireSetTO();
            if (String.IsNullOrEmpty(name))
            {
                result.fault = new FaultTO("Missing set name");
                return result;
            }
            try
            {
                QuestionnaireSet mdo = QuestionnaireSet.getSet(name);
                result = new QuestionnaireSetTO(mdo);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }
    }
}
