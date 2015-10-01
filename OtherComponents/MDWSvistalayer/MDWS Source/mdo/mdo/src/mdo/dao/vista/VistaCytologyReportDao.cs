using System;
using System.Collections.Generic;
using System.Collections;
using System.Collections.Specialized;
using System.Text;
using System.IO;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.src.mdo.dao.vista;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaCytologyReportDao: ICytologyReportsDao 
    {
        AbstractConnection cxn = null;

        public VistaCytologyReportDao(AbstractConnection cxn)
        {
            this.cxn = cxn;
        }

        public CytologyReport[] getCytologyReports(string fromDate, string toDate, int nrpts)
        {
            return getCytologyReports(cxn.Pid, fromDate, toDate, nrpts);
        }

        public CytologyReport[] getCytologyReports(string dfn, string fromDate, string toDate, int nrpts)
        {
            string request = buildGetCytologyReportsRequest(dfn, fromDate, toDate, nrpts);
            string response = (string)cxn.query(request);
            return toCytologyReports(response);
        }

        internal string buildGetCytologyReportsRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_CY:CYTOLOGY~CY;ORDV02A;0;");
        }

        internal CytologyReport[] toCytologyReports(string response)
        {
            if (response == "")
            {
                return null;
            }
            ArrayList lst = new ArrayList();
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            CytologyReport rpt = null;
            string collectionDate = "";
            string specimenDesc = "";
            string accessionNum = "";
            for (int i = 0; i < lines.Length; i++)
            {
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                int fldnum = Convert.ToInt32(flds[0]);
                switch (fldnum)
                {
                    case 1:
                        if (rpt != null)
                        {
                            rpt.Specimen = new LabSpecimen("", specimenDesc.Substring(0, specimenDesc.Length - 1), collectionDate, "");
                            rpt.Specimen.AccessionNumber = accessionNum;
                            lst.Add(rpt);
                        }
                        rpt = new CytologyReport();
                        rpt.Title = "Cytopathology Report";
                        specimenDesc = "";
                        if (flds.Length == 2)
                        {
                            string[] parts = StringUtils.split(flds[1], StringUtils.SEMICOLON);
                            if (parts.Length == 2)
                            {
                                rpt.Facility = new SiteId(parts[0], parts[1]);
                            }
                            else if (flds[1] != "")
                            {
                                rpt.Facility = new SiteId(cxn.DataSource.SiteId.Id, flds[1]);
                            }
                            else
                            {
                                rpt.Facility = cxn.DataSource.SiteId;
                            }
                        }
                        break;
                    case 2:
                        if (flds.Length == 2)
                        {
                            collectionDate = VistaTimestamp.toUtcFromRdv(flds[1]);
                        }
                        break;
                    case 3:
                        if (flds.Length == 2)
                        {
                            specimenDesc += flds[1] + '\n';
                        }
                        break;
                    case 4:
                        if (flds.Length == 2)
                        {
                            accessionNum = flds[1];
                        }
                        break;
                    case 5:
                        if (flds.Length == 2)
                        {
                            rpt.Exam += flds[1] + '\n';
                        }
                        break;
                }
            }

            if (rpt != null)
            {
                rpt.Specimen = new LabSpecimen("", specimenDesc.Substring(0, specimenDesc.Length - 1), collectionDate, "");
                rpt.Specimen.AccessionNumber = accessionNum;
                lst.Add(rpt);
            }
            return (CytologyReport[])lst.ToArray(typeof(CytologyReport));
        }

    }
}
