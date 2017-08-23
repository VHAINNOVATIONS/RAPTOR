using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdws.dto;
using gov.va.medora.mdo.api;

namespace gov.va.medora.mdws.lib
{
    public class ProblemLib
    {
        MySession _mySession;

        public ProblemLib(MySession mySession)
        {
            _mySession = mySession;
        }

        public TaggedProblemArray getProblems(String status)
        {
            TaggedProblemArray result = new TaggedProblemArray();

            string msg = MdwsUtils.isAuthorizedConnection(_mySession);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                result = new TaggedProblemArray(_mySession.ConnectionSet.BaseConnection.DataSource.SiteId.Id,
                    new ProblemApi().getProblems(_mySession.ConnectionSet.BaseConnection, status));
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }


        public TaggedProblemArrays getProblemsFromSites(String status)
        {
            TaggedProblemArrays result = new TaggedProblemArrays();

            string msg = MdwsUtils.isAuthorizedConnection(_mySession);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                result = new TaggedProblemArrays(new ProblemApi().getProblems(_mySession.ConnectionSet, status));
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

    }
}