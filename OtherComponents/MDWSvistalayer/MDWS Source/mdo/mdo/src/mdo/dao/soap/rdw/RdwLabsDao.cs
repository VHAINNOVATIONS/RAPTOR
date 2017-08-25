using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.exceptions;
using System.Collections;
using gov.va.medora.utils;
using gov.va.medora.mdo.dao.vista;
using gov.va.medora.mdo.rdw;

namespace gov.va.medora.mdo.dao.soap.rdw
{
    public class RdwLabsDao : ILabsDao
    {
        RdwConnection _cxn;

        public RdwLabsDao(AbstractConnection cxn)
        {
            if (!(cxn is RdwConnection))
            {
                throw new MdoException("Invalid connection");
            }
            _cxn = (RdwConnection)cxn;
        }


        public MicrobiologyReport[] getMicrobiologyReports(string fromDate, string toDate, int nrpts)
        {
            return getMicrobiologyReports(_cxn.Pid, fromDate, toDate, nrpts);
        }

        internal MicrobiologyReport[] getMicrobiologyReports(string pid, string fromDate, string toDate, int nrpts)
        {
            MDWSRPCs rdw = new MDWSRPCs() { Url = _cxn.DataSource.Provider }; // the cookie URL should have been set on the connection
            string response = rdw.getMicroNotes(pid, Convert.ToInt64(nrpts), true, fromDate, toDate);
            return toMicrobiologyReports(response);
        }

        internal MicrobiologyReport[] toMicrobiologyReports(string response)
        {
            if (response == "")
            {
                return null;
            }
            ArrayList lst = new ArrayList();
            string[] lines = StringUtils.split(response, '\n');
            lines = StringUtils.trimArray(lines);
            MicrobiologyReport rpt = null;
            for (int i = 0; i < lines.Length; i++)
            {
                lines[i] = lines[i].Replace("<\\Line>", "");
                int firstGT = lines[i].IndexOf(">");
                lines[i] = lines[i].Substring(firstGT + 1, lines[i].Length - firstGT - 1);
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                if (!StringUtils.isNumeric(flds[0])) // the last two lines appear to be the RPC string and the number of results - just ignoring for now but may be useful later
                {
                    continue;
                }
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
                                rpt.Facility = new SiteId(_cxn.DataSource.SiteId.Id, flds[1]);
                            }
                            else
                            {
                                rpt.Facility = _cxn.DataSource.SiteId;
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
                            rpt.Specimen = new LabSpecimen("", flds[1], "", "");
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

        #region Not Implemented Members
        public string getAllLabReports(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getAllLabReports(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public SurgicalPathologyReport[] getSurgicalPathologyReports(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public SurgicalPathologyReport[] getSurgicalPathologyReports(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getBloodAvailabilityReport(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getBloodAvailabilityReport(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getBloodTransfusionReport(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getBloodTransfusionReport(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getBloodBankReport()
        {
            throw new NotImplementedException();
        }

        public string getBloodBankReport(string pid)
        {
            throw new NotImplementedException();
        }

        public string getElectronMicroscopyReport(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getElectronMicroscopyReport(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getCytopathologyReport()
        {
            throw new NotImplementedException();
        }

        public string getCytopathologyReport(string pid)
        {
            throw new NotImplementedException();
        }

        public string getAutopsyReport()
        {
            throw new NotImplementedException();
        }

        public string getAutopsyReport(string pid)
        {
            throw new NotImplementedException();
        }

        public string getLrDfn(string pid)
        {
            throw new NotImplementedException();
        }

        public IList<LabTest> getTests(string target)
        {
            throw new NotImplementedException();
        }

        public string getTestDescription(string identifierString)
        {
            throw new NotImplementedException();
        }

        MicrobiologyReport[] ILabsDao.getMicrobiologyReports(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        #endregion


    }
}
