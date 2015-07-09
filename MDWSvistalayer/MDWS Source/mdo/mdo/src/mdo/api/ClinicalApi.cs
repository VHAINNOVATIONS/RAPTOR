using System;
using System.Collections.Generic;
using System.Collections;
using System.Collections.Specialized;
using System.Xml;
using System.IO;
using gov.va.medora.mdo.dao;

namespace gov.va.medora.mdo.api
{
    public class ClinicalApi
    {
        const string DAO_NAME = "IClinicalDao";
    	
	    public ClinicalApi() {}

        #region Radiology
        public static IndexedHashtable getRadiologyReports(ConnectionSet cxns, string fromDate, string toDate, int nrpts)
        {
            return cxns.query("IRadiologyDao", "getRadiologyReports", new object[] { fromDate,toDate,nrpts });
        }

        public static IndexedHashtable getRadiologyReportsBySite(ConnectionSet cxns, string fromDate, string toDate, string siteCode)
        {
            return cxns.query("IRadiologyDao", "getRadiologyReportsBySite", new object[] { fromDate, toDate, siteCode });
        }

        #endregion

        #region Allergy
        public static IndexedHashtable getAllergies(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getAllergies", new object[] { });
        }

        public static IndexedHashtable getAllergiesBySite(ConnectionSet cxns, string siteCode)
        {
            return cxns.query(DAO_NAME, "getAllergiesBySite", new object[] { siteCode });
        }
        #endregion

        public static IndexedHashtable getProblemList(ConnectionSet cxns, string type)
        {
            return cxns.query(DAO_NAME, "getProblemList", new object[] { type });
        }

        public static IndexedHashtable getFluRelatedProblemList(ConnectionSet cxns)
        {
            List<string> fluCodes = loadFluCodes();
            return cxns.query(DAO_NAME, "getFluRelatedProblemList", new object[] { fluCodes });
        }

        internal static List<string> loadFluCodes()
        {
            string filepath = utils.ResourceUtils.ResourcesPath + "data\\fluIcd9.xml";
            if (String.IsNullOrEmpty(filepath))
            {
                throw new ArgumentNullException("filepath");
            }
            if (!File.Exists(filepath))
            {
                throw new ArgumentException("Missing XML flu ICD-9 definition file");
            }

            List<string> result = null;
            XmlReader reader = new XmlTextReader(filepath);
            while (reader.Read())
            {
                switch ((int)reader.NodeType)
                {
                    case (int)XmlNodeType.Element:
                        string name = reader.Name;
                        if (name == "RelatedDxs")
                        {
                            result = new List<string>();
                        }
                        else if (name == "Diagnosis")
                        {
                            string codes = reader.GetAttribute("icd9");
                            string[] parts = codes.Split(new char[] { ',' });
                            for (int i = 0; i > parts.Length; i++)
                            {
                                result.Add(parts[i]);
                            }
                        }
                        break;
                    case (int)XmlNodeType.EndElement:
                        name = reader.Name;
                        if (name == "RelatedDxs")
                        {
                        }
                        else if (name == "Diagnosis")
                        {
                        }
                        break;
                }
            }
            return result;
        }

        #region Surgery

        public static IndexedHashtable getSurgeryReports(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getSurgeryReports", new object[] { false });
        }

        public static IndexedHashtable getSurgeryReportsBySite(ConnectionSet cxns, string siteCode)
        {
            return cxns.query(DAO_NAME, "getSurgeryReportsBySite", new object[] { siteCode });
        }

        public string getSurgeryReportText(AbstractConnection cxn, string rptId)
        {
            return ((IClinicalDao)cxn.getDao(DAO_NAME)).getSurgeryReportText(rptId);
        }

        public static IndexedHashtable getSurgeryReports(ConnectionSet cxns, bool fWithText)
        {
            return cxns.query(DAO_NAME, "getSurgeryReports", new object[] { fWithText });
        }

        #endregion

        public MdoDocument[] getHealthSummaryList(AbstractConnection cxn)
        {
            return ((IClinicalDao)cxn.getDao(DAO_NAME)).getHealthSummaryList();
        }

        public IndexedHashtable getHealthSummaryList(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getHealthSummaryList", new object[] { });
        }

        public string getHealthSummaryTitle(AbstractConnection cxn, string summaryId)
        {
            return ((IClinicalDao)cxn.getDao(DAO_NAME)).getHealthSummaryTitle(summaryId);
        }

        public IndexedHashtable getHealthSummaryTitle(ConnectionSet cxns, string summaryId)
        {
            return cxns.query(DAO_NAME, "getHealthSummaryTitle", new object[] { summaryId });
        }

        public string getHealthSummaryText(AbstractConnection cxn, string mpiPid, MdoDocument hs, String sourceSiteId)
        {
            return ((IClinicalDao)cxn.getDao(DAO_NAME)).getHealthSummaryText(mpiPid, hs, sourceSiteId);
        }

        public static string getAdHocHealthSummaryByDisplayName(AbstractConnection cxn, string displayName)
        {
            return ((IClinicalDao)cxn.getDao(DAO_NAME)).getAdHocHealthSummaryByDisplayName(displayName);
        }

        public static IndexedHashtable getAdHocHealthSummaryByDisplayName(ConnectionSet cxns, string displayName)
        {
            return cxns.query(DAO_NAME, "getAdHocHealthSummaryByDisplayName", new object[] { displayName });
        }

        public static HealthSummary getHealthSummary(AbstractConnection cxn, MdoDocument hs)
        {
            return ((IClinicalDao)cxn.getDao(DAO_NAME)).getHealthSummary(hs);
        }

        public static IndexedHashtable getHealthSummary(ConnectionSet cxns, MdoDocument hs)
        {
            return cxns.query(DAO_NAME, "getHealthSummary", new object[] { hs });
        }

        public static string getNhinData(AbstractConnection cxn, string types = null)
        {
            return ((IClinicalDao)cxn.getDao(DAO_NAME)).getNhinData(types);
        }

        public static string getNhinData(AbstractConnection cxn, string dfn, string types = null)
        {
            return ((IClinicalDao)cxn.getDao(DAO_NAME)).getNhinData(dfn, types);
        }

        public static IndexedHashtable getNhinData(ConnectionSet cxns, string types = null, string validTypes = null)
        {
            return cxns.query(DAO_NAME, "getNhinData", new object[] { types, validTypes });
        }

        public static IndexedHashtable getPatientRecord(ConnectionSet cxns, string types)
        {
            return cxns.query(DAO_NAME, "getPatientRecord", new object[1] { types });
        }

        #region Clinic Directory

        public static IndexedHashtable getStaffByCriteria(ConnectionSet cxns, string siteCode, string searchTerm, string firstName, string lastName, string type)
        {
            return cxns.query(DAO_NAME, "getStaffByCriteria", new object[] { siteCode, searchTerm, firstName, lastName, type });
        }

        #endregion

    }
}
