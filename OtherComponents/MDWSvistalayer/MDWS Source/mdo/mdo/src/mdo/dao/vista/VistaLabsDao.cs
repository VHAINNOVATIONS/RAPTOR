using System;
using System.Collections.Generic;
using System.Collections;
using System.Collections.Specialized;
using System.Text;
using System.IO;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.src.mdo;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaLabsDao : ILabsDao
    {
        AbstractConnection cxn = null;

        public VistaLabsDao(AbstractConnection cxn)
        {
            this.cxn = cxn;
        }

        #region
        #endregion

        #region LRDFN
        /// <summary>
        /// Turn a DFN in to a LRDFN. Can be a long running query as it traverses the entire patient file until a record is found with the specified LRDFN. 
        /// Only use if you absolutely need to find the patient's DFN when starting with only the LRDFN!
        /// </summary>
        /// <param name="lrdfn">LAB DATA (63) file ID</param>
        /// <returns></returns>
        public string getDfnFromLrdfn(string lrdfn)
        {
            MdoQuery request = buildGetDfnFromLrdfnRequest(lrdfn);
            string response = (string)cxn.query(request);
            return response;
        }
        
        internal MdoQuery buildGetDfnFromLrdfnRequest(string lrdfn)
        {
            DdrLister ddr = new DdrLister(this.cxn);
            ddr.File = "2";
            ddr.Fields = ".01";
            ddr.Flags = "IP";
            ddr.Max = "1";
            ddr.Xref = "#";
            ddr.Screen = String.Concat("I $P($G(^(\"LR\")),U,1)=", lrdfn);
            return ddr.buildRequest();
        }


        public string getLrDfn()
        {
            return getLrDfn(cxn.Pid);
        }

        public string getLrDfn(string dfn)
        {
            MdoQuery request = buildGetLrDfnRequest(dfn);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetLrDfnRequest(string dfn)
        {
			VistaUtils.CheckRpcParams(dfn);
            string arg = "$G(^DPT(\"" + dfn + "\",\"LR\"))";
            return VistaUtils.buildGetVariableValueRequest(arg);
        }

        #endregion

        #region Anatomic Pathology

        public AnatomicPathologyReport[] getAnatomicPathologyReports(string fromDate, string toDate, int nrpts)
        {
            return getAnatomicPathologyReports(cxn.Pid, fromDate, toDate, nrpts);
        }

        public AnatomicPathologyReport[] getAnatomicPathologyReports(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = null;
            string response = "";
            try
            {
                request = buildGetAnatomicPathologyReportsRequest(dfn, fromDate, toDate, nrpts);
                response = (string)cxn.query(request);
                return toAnatomicPathologyReports(response);
            }
            catch (Exception exc)
            {
                throw new MdoException(request, response, exc);
            }
        }

        internal MdoQuery buildGetAnatomicPathologyReportsRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            VistaUtils.CheckRpcParams(dfn, fromDate, toDate);
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_APR:ANATOMIC PATHOLOGY~SP;ORDV02A;0;");
        }

        internal AnatomicPathologyReport[] toAnatomicPathologyReports(string response)
        {
            if (response == "")
            {
                return null;
            }
            ArrayList lst = new ArrayList();
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            AnatomicPathologyReport rpt = null;
            for (int i = 0; i < lines.Length; i++)
            {
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                int fldnum = Convert.ToInt32(flds[0]);
                switch (fldnum)
                {
                    case 1:
                        if (rpt != null)
                        {
                            lst.Add(rpt);
                        }
                        rpt = new AnatomicPathologyReport();
                        rpt.Title = "Anatomic Pathology Report";
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
                            rpt.Timestamp = VistaTimestamp.toUtcFromRdv(flds[1]);
                        }
                        break;
                    case 3:
                        if (flds.Length == 2)
                        {
                            rpt.Specimen = new LabSpecimen("", flds[1], "", "");
                        }
                        break;
                    case 4: //[DP] 6/20/2011 Allow for the case with a null specimen.
                        if (flds.Length == 2  && rpt.Specimen != null)
                        {
                            rpt.Specimen.AccessionNumber = flds[1];
                        }
                        break;
                    case 5:
                        if (flds.Length == 2)
                        {
                            if (!String.IsNullOrEmpty(flds[1].TrimEnd()))
                            {
                                rpt.Exam += flds[1] + '\n';
                            }
                        }
                        break;
                }
            }

            if (rpt != null)
            {
                lst.Add(rpt);
            }
            return (AnatomicPathologyReport[])lst.ToArray(typeof(AnatomicPathologyReport));
        }

        #endregion

        #region Surgical Pathology

        public SurgicalPathologyReport[] getSurgicalPathologyReports(string fromDate, string toDate, int nrpts)
        {
            return getSurgicalPathologyReports(cxn.Pid, fromDate, toDate, nrpts);
        }

        public SurgicalPathologyReport[] getSurgicalPathologyReports(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = null;
            string response = "";
            try
            {
                request = buildGetSurgicalPathologyReportsRequest(dfn, fromDate, toDate, nrpts);
                response = (string)cxn.query(request);
                return toSurgicalPathologyReports(response);
            }
            catch (Exception exc)
            {
                throw new MdoException(request, response, exc);
            }
        }

        internal MdoQuery buildGetSurgicalPathologyReportsRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            VistaUtils.CheckRpcParams(dfn, fromDate, toDate);
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_SP:SURGICAL PATHOLOGY~SP;ORDV02A;0;");
        }

        internal SurgicalPathologyReport[] toSurgicalPathologyReports(string response)
        {
            if (response == "")
            {
                return null;
            }
            ArrayList lst = new ArrayList();
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            SurgicalPathologyReport rpt = null;
            string specimenName = "";
            string exam = "";
            string accessionNum = "";

            IList<SurgicalPathologyReport> rpts = new List<SurgicalPathologyReport>();
            SurgicalPathologyReport newRpt = new SurgicalPathologyReport();

            for (int i = 0; i < lines.Length; i++)
            {
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                int fldnum = Convert.ToInt32(flds[0]);
                switch (fldnum)
                {
                    case 1:
                        newRpt.Title = "Surgical Pathology Report";
                        if (rpt != null)
                        {
                            if (!String.IsNullOrEmpty(specimenName))
                            {
                                specimenName = specimenName.Substring(0, specimenName.Length - 1);
                                rpt.Specimen = new LabSpecimen("", specimenName, "", accessionNum);
                            }
                            if (!String.IsNullOrEmpty(exam))
                            {
                                exam = exam.Substring(0, exam.Length - 1);
                                rpt.Exam = exam;
                            }
                            lst.Add(rpt);
                        }
                        rpt = new SurgicalPathologyReport();
                        rpt.Title = "Surgical Pathology Report";
                        if (flds.Length == 2)
                        {
                            string[] parts = StringUtils.split(flds[1], StringUtils.SEMICOLON);
                            if (parts.Length == 2)
                            {
                                newRpt.Facility = new SiteId(parts[0], parts[1]);
                                rpt.Facility = new SiteId(parts[0], parts[1]);
                            }
                            else if (flds[1] != "")
                            {
                                newRpt.Facility = new SiteId(cxn.DataSource.SiteId.Id, parts[1]);
                                rpt.Facility = new SiteId(cxn.DataSource.SiteId.Id, flds[1]);
                            }
                            else
                            {
                                newRpt.Facility = cxn.DataSource.SiteId;
                                rpt.Facility = cxn.DataSource.SiteId;
                            }
                        }
                        break;
                    case 2:
                        if (flds.Length == 2)
                        {
                            newRpt.Timestamp = VistaTimestamp.toUtcFromRdv(flds[1]);
                            rpt.Timestamp = VistaTimestamp.toUtcFromRdv(flds[1]);
                        }
                        break;
                    case 3:
                        if (flds.Length == 2)
                        {
                            if (newRpt.Specimen == null)
                            {
                                newRpt.Specimen = new LabSpecimen("", "", "", "");
                            }
                            newRpt.Specimen.Name += flds[1] + '\n';
                            specimenName += flds[1] + '\n';
                        }
                        break;
                    case 4:
                        if (flds.Length == 2)
                        {
                            if (newRpt.Specimen == null)
                            {
                                newRpt.Specimen = new LabSpecimen("", "", "", "");
                            }
                            newRpt.Specimen.AccessionNumber = flds[1];
                            accessionNum = flds[1];
                        }
                        break;
                    case 5:
                        if (flds.Length == 2)
                        {
                            if (!String.IsNullOrEmpty(flds[1].TrimEnd()))
                            {
                                newRpt.Exam += flds[1] + '\n';
                                exam += flds[1] + '\n';
                            }
                        }
                        break;
                    case 6:
                        if (newRpt.Specimen != null && !String.IsNullOrEmpty(newRpt.Specimen.Name))
                        {
                            newRpt.Specimen.Name = newRpt.Specimen.Name.TrimEnd(new char[] { '\n' });
                        }
                        if (!String.IsNullOrEmpty(newRpt.Exam))
                        {
                            newRpt.Exam = newRpt.Exam.TrimEnd(new char[] { '\n' });
                        }
                        rpts.Add(newRpt);
                        newRpt = new SurgicalPathologyReport();
                        break;
                }
            }

            if (rpt != null)
            {
                lst.Add(rpt);
            }

            SurgicalPathologyReport[] result = new SurgicalPathologyReport[rpts.Count];
            rpts.CopyTo(result, 0);
            return result;
            //return (SurgicalPathologyReport[])lst.ToArray(typeof(SurgicalPathologyReport));
        }

        #endregion

        #region Microbiology

        public MicrobiologyReport[] getMicrobiologyReports(string fromDate, string toDate, int nrpts)
        {
            return getMicrobiologyReports(cxn.Pid, fromDate, toDate, nrpts);
        }

        public MicrobiologyReport[] getMicrobiologyReports(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetMicrobiologyReportsRequest(dfn, fromDate, toDate, nrpts);
            string response = (string)cxn.query(request);
            return toMicrobiologyReports(response);
        }

        internal MdoQuery buildGetMicrobiologyReportsRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            VistaUtils.CheckRpcParams(dfn, fromDate, toDate);
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_MIC:MICROBIOLOGY~MI;ORDV05;38;");
        }

        internal MicrobiologyReport[] toMicrobiologyReports(string response)
        {
            if (response == "")
            {
                return null;
            }
            ArrayList lst = new ArrayList();
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            MicrobiologyReport rpt = null;
            for (int i = 0; i < lines.Length; i++)
            {
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                int fldnum = Convert.ToInt32(flds[0]);
                switch (fldnum)
                {
                    case 1:
                        if (rpt != null)
                        {
                            if (!String.IsNullOrEmpty(rpt.Sample))
                            {
                                rpt.Sample = rpt.Sample.Substring(0, rpt.Sample.Length - 1);
                            }
                            lst.Add(rpt);
                        }
                        rpt = new MicrobiologyReport();
                        rpt.Title = "Microbiology Report";
                        if (flds.Length == 2)
                        {
                            string[] parts = StringUtils.split(flds[1], StringUtils.SEMICOLON);
                            if (parts.Length == 2)
                            {
                                rpt.Facility = new SiteId(parts[1], parts[0]);
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
                            rpt.Timestamp = VistaTimestamp.toUtcFromRdv(flds[1]);
                        }
                        break;
                    case 3:
                        if (flds.Length == 2)
                        {
                            rpt.Title = flds[1];
                        }
                        break;
                    case 4:
                        if (flds.Length == 2)
                        {
                            rpt.Sample += flds[1] + '\n';
                        }
                        break;
                    case 5:
                        if (flds.Length == 2)
                        {
                            rpt.Specimen = new LabSpecimen("",flds[1],"","");
                        }
                        break;
                    case 6:
                        if (flds.Length == 2)
                        {
                            rpt.Specimen.AccessionNumber = flds[1];
                        }
                        break;
                    case 7:
                        if (flds.Length == 2)
                        {
                            rpt.Text += flds[1] + '\n';
                        }
                        break;
                }
            }

            if (rpt != null)
            {
                lst.Add(rpt);
            }
            return (MicrobiologyReport[])lst.ToArray(typeof(MicrobiologyReport));
        }

        #endregion

        #region Cytology

        public CytologyReport[] getCytologyReports(string fromDate, string toDate, int nrpts)
        {
            return getCytologyReports(cxn.Pid, fromDate, toDate, nrpts);
        }

        public CytologyReport[] getCytologyReports(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetCytologyReportsRequest(dfn, fromDate, toDate, nrpts);
            string response = (string)cxn.query(request);
            return toCytologyReports(response);
        }

        internal MdoQuery buildGetCytologyReportsRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            VistaUtils.CheckRpcParams(dfn, fromDate, toDate);
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
                            // ok for report to not have a specimen
                            if (!String.IsNullOrEmpty(specimenDesc))
                            {
                                rpt.Specimen = new LabSpecimen("", specimenDesc.Substring(0, specimenDesc.Length - 1), collectionDate, "");
                                rpt.Specimen.AccessionNumber = accessionNum;
                            }
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
                // ok for report to not have a specimen
                if (!String.IsNullOrEmpty(specimenDesc))
                {
                    rpt.Specimen = new LabSpecimen("", specimenDesc.Substring(0, specimenDesc.Length - 1), collectionDate, "");
                    rpt.Specimen.AccessionNumber = accessionNum;
                }
                lst.Add(rpt);
            }
            return (CytologyReport[])lst.ToArray(typeof(CytologyReport));
        }


        #endregion

        #region Cytopathology

        public string getCytopathologyReport()
        {
            return getCytopathologyReport(cxn.Pid);
        }

        public string getCytopathologyReport(string dfn)
        {
            MdoQuery request = buildGetCytopathologyReportRequest(dfn);
            string response = (string)cxn.query(request);
            response = StringUtils.trimCrLf(response);
            if (response == "No Cytology reports available...")
            {
                response = "No Cytopathology reports available...";
            }
            return response;
        }

        internal MdoQuery buildGetCytopathologyReportRequest(string dfn)
        {
            VistaUtils.CheckRpcParams(dfn);
            return VistaUtils.buildReportTextRequest(dfn, "", "", 0, "26:CYTOPATHOLOGY~;;0");
        }

        #endregion

        #region Electron Microscopy

        public ElectronMicroscopyReport[] getElectronMicroscopyReports(string fromDate, string toDate, int nrpts)
        {
            return getElectronMicroscopyReports(cxn.Pid, fromDate, toDate, nrpts);
        }

        public ElectronMicroscopyReport[] getElectronMicroscopyReports(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = null;
            string response = "";
            try
            {
                request = buildGetElectronMicroscopyReportsRequest(dfn, fromDate, toDate, nrpts);
                response = (string)cxn.query(request);
                return toElectronMicroscopyReports(response);
            }
            catch (Exception exc)
            {
                throw new MdoException(request, response, exc);
            }
        }

        internal MdoQuery buildGetElectronMicroscopyReportsRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            VistaUtils.CheckRpcParams(dfn, fromDate, toDate);
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_EM:ELECTRON MICROSCOPY~EM;ORDV02A;0;");
        }

        internal ElectronMicroscopyReport[] toElectronMicroscopyReports(string response)
        {
            if (response == "")
            {
                return null;
            }
            ArrayList lst = new ArrayList();
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            ElectronMicroscopyReport rpt = null;
            string specimenName = "";
            string exam = "";
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
                            if (!String.IsNullOrEmpty(specimenName))
                            {
                                specimenName = specimenName.Substring(0, specimenName.Length - 1);
                                rpt.Specimen = new LabSpecimen("", specimenName, "", accessionNum);
                            }
                            if (!String.IsNullOrEmpty(exam))
                            {
                                exam = exam.Substring(0, exam.Length - 1);
                                rpt.Exam = exam;
                                exam = "";
                            }
                            lst.Add(rpt);
                        }
                        rpt = new ElectronMicroscopyReport();
                        rpt.Title = "Electron Microscopy Report";
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
                            rpt.Timestamp = VistaTimestamp.toUtcFromRdv(flds[1]);
                        }
                        break;
                    case 3:
                        if (flds.Length == 2)
                        {
                            specimenName += flds[1] + '\n';
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
                            if (!String.IsNullOrEmpty(flds[1].TrimEnd()))
                            {
                                exam += flds[1] + '\n';
                            }
                        }
                        break;
                }
            }

            if (rpt != null)
            {
                if (!String.IsNullOrEmpty(specimenName))
                {
                    specimenName = specimenName.Substring(0, specimenName.Length - 1);
                }
                rpt.Specimen = new LabSpecimen("", specimenName, "", accessionNum);
                if (!String.IsNullOrEmpty(exam))
                {
                    exam = exam.Substring(0, exam.Length - 1);
                }
                rpt.Exam = exam;
                exam = "";

                lst.Add(rpt);
            }
            return (ElectronMicroscopyReport[])lst.ToArray(typeof(ElectronMicroscopyReport));
        }

        // This is how it used to work.  Just returned a text report.  Needs to be removed.
        public string getElectronMicroscopyReport(string fromDate, string toDate, int nrpts)
        {
            return getElectronMicroscopyReport(cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getElectronMicroscopyReport(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetElectronMicroscopyReportRequest(dfn, fromDate, toDate, nrpts);
            string response = (string)cxn.query(request);
            return StringUtils.trimCrLf(response);
        }

        internal MdoQuery buildGetElectronMicroscopyReportRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            VistaUtils.CheckRpcParams(dfn, fromDate, toDate);
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_EM:ELECTRON MICROSCOPY~EM;ORDV02A;0;");
        }

        #endregion

        #region Blood Availability

        public string getBloodAvailabilityReport(string fromDate, string toDate, int nrpts)
        {
            return getBloodAvailabilityReport(cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getBloodAvailabilityReport(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetBloodAvailabilityReportRequest(dfn, fromDate, toDate, nrpts);
            string response = (string)cxn.query(request);
            return StringUtils.trimCrLf(response);
        }

        internal MdoQuery buildGetBloodAvailabilityReportRequest(
            string dfn, string fromDate, string toDate, int nrpts)
        {
            VistaUtils.CheckRpcParams(dfn, fromDate, toDate);
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_BA:BLOOD AVAILABILITY~;;45;");
        }

        #endregion

        #region Blood Transfusion

        public string getBloodTransfusionReport(string fromDate, string toDate, int nrpts)
        {
            return getBloodTransfusionReport(cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getBloodTransfusionReport(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetBloodTransfusionReportRequest(dfn, fromDate, toDate, nrpts);
            string response = (string)cxn.query(request);
            return StringUtils.trimCrLf(response);
        }

        internal MdoQuery buildGetBloodTransfusionReportRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            VistaUtils.CheckRpcParams(dfn, fromDate, toDate);
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_BT:BLOOD TRANSFUSION~;;36;");
        }

        #endregion

        #region Blood Bank

        public string getBloodBankReport()
        {
            return getBloodBankReport(cxn.Pid);
        }

        public string getBloodBankReport(string dfn)
        {
            MdoQuery request = buildGetBloodBankReportRequest(dfn);
            string response = (string)cxn.query(request);
            return StringUtils.trimCrLf(response);
        }

        internal MdoQuery buildGetBloodBankReportRequest(string dfn)
        {
            VistaUtils.CheckRpcParams(dfn);
            return VistaUtils.buildReportTextRequest_AllResults(dfn, "2:BLOOD BANK REPORT~;;0");
        }

        #endregion

        #region Autopsy

        public string getAutopsyReport()
        {
            return getAutopsyReport(cxn.Pid);
        }

        public string getAutopsyReport(string dfn)
        {
            MdoQuery request = buildGetAutopsyReportRequest(dfn);
            string response = (string)cxn.query(request);
            return toReportFromAutopsyResponse(response);
        }

        internal string toReportFromAutopsyResponse(string response)
        {
            return StringUtils.trimCrLf(response);
        }

        internal MdoQuery buildGetAutopsyReportRequest(string dfn)
        {
            VistaUtils.CheckRpcParams(dfn);
            return VistaUtils.buildReportTextRequest(dfn, "", "", 0, "OR_AU:AUTOPSY~;;0");
        }

        #endregion

        #region Lookup Tables

        internal StringDictionary getSpecimenTypes()
        {
            return cxn.SystemFileHandler.getLookupTable(VistaConstants.TOPOGRAPHY_FIELD);
        }

        #endregion

        #region Lab Test Descriptions
        public IList<LabTest> getTests(string target)
        {
            MdoQuery request = buildGetTestsQuery(target);
            string response = (string)cxn.query(request);
            return toTests(response);
        }

        internal MdoQuery buildGetTestsQuery(string target)
        {
            VistaQuery vq = new VistaQuery("ORWLRR ALLTESTS");
            if (String.IsNullOrEmpty(target))
            {
                vq.addParameter(vq.LITERAL, "");
            }
            else
            {
                vq.addParameter(vq.LITERAL, target);
            }
            vq.addParameter(vq.LITERAL, "1");
            return vq;
        }

        internal IList<LabTest> toTests(string response)
        {
            IList<LabTest> result = new List<LabTest>();

            if (!String.IsNullOrEmpty(response))
            {
                string[] lines = response.Split(new string[] { StringUtils.CRLF }, StringSplitOptions.RemoveEmptyEntries);

                foreach (string line in lines)
                {
                    string[] pieces = line.Split(new string[] { StringUtils.CARET }, StringSplitOptions.None);

                    if (pieces == null || pieces.Length != 2)
                    {
                        continue;
                    }

                    LabTest test = new LabTest() { Id = line, Name = pieces[1] }; // ID is the whole line - the call to getTestDescription takes the whole thing so we won't separate
                    result.Add(test);
                }
            }

            return result;
        }

        public string getTestDescription(string identifierString)
        {
            MdoQuery request = buildGetTestDescriptionQuery(identifierString);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetTestDescriptionQuery(string identifierString)
        {
            VistaQuery vq = new VistaQuery("ORWLRR INFO");
            vq.addParameter(vq.LITERAL, identifierString);
            return vq;
        }
        #endregion

        #region Chemistry Hematology

        public IList<LabPanel> getAllChemHemTests(string dfn)
        {
            MdoQuery request = buildGetAllChemHemTestsQuery(getLrDfn(dfn));
            String response = (String)cxn.query(request);
            return toAllChemHemTests(response);
        }

        internal MdoQuery buildGetAllChemHemTestsQuery(String lrDfn)
        {
            DdrLister ddr = new DdrLister(this.cxn);
            ddr.Fields = "";
            ddr.File = "63.04";
            ddr.Flags = "IP";
            ddr.Iens = String.Concat(",", lrDfn, ",");
            ddr.Xref = "#";
            ddr.Id = "";
            return ddr.buildRequest();
        }

        private IList<LabPanel> toAllChemHemTests(string response)
        {
            throw new NotImplementedException();
        }

        #endregion

        public string getAllLabReports(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getAllLabReports(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }
    }
}
