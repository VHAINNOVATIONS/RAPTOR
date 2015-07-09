using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.dao;

namespace gov.va.medora.mdo.api
{
    public class ProblemApi
    {
        const string DAO_NAME = "IProblemDao";

        public ProblemApi() { }

        public IndexedHashtable getProblems(ConnectionSet cxns, String status)
        {
            return cxns.query(DAO_NAME, "getProblems", new object[] { status });
        }

        public IList<Problem> getProblems(AbstractConnection cxn, String status)
        {
            return ((IProblemDao)cxn.getDao(DAO_NAME)).getProblems(status);
        }
    }
}
