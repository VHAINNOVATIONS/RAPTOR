using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo.dao;

namespace gov.va.medora.mdo
{
    public class Consult : Order
    {
        KeyValuePair<string, string> service;
        string title;
        string requestedProcedure;

        public Consult() { }

        public KeyValuePair<string, string> Service
        {
            get { return service; }
            set { service = value; }
        }

        public string Title
        {
            get { return title; }
            set { title = value; }
        }

        public string RequestedProcedure
        {
            get { return requestedProcedure; }
            set { requestedProcedure = value; }
        }

        const string DAO_NAME = "IConsultDao";

        internal static new IConsultDao getDao(AbstractConnection cxn)
        {
            AbstractDaoFactory f = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(cxn.DataSource.Protocol));
            return f.getConsultDao(cxn);
        }

        public static string getConsultNote(AbstractConnection cxn, string consultId)
        {
            return getDao(cxn).getConsultNote(consultId);
        }

        public static IndexedHashtable getConsultsForPatient(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getConsultsForPatient", new object[] { });
        }

        public Consult[] getConsultsForPatient(AbstractConnection cxn)
        {
            return getDao(cxn).getConsultsForPatient();
        }

        public Consult[] getConsultsForPatient(AbstractConnection cxn, string pid)
        {
            return getDao(cxn).getConsultsForPatient(pid);
        }
    }
}
