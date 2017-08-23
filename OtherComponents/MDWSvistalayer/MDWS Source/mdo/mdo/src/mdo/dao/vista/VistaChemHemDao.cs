using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Collections;
using System.Text;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.src.mdo;
using System.Xml;
using System.IO;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaChemHemDao : IChemHemDao
    {
        AbstractConnection cxn;

        public VistaChemHemDao(AbstractConnection cxn)
        {
            this.cxn = cxn;
        }

        //=========================================================================================
        // Chem/Hem DDR + CPRS RPC
        //=========================================================================================
        public ChemHemReport[] getChemHemReports(string fromDate, string toDate)
        {
            return getChemHemReports(cxn.Pid, fromDate, toDate);
        }

        /*
         * In order to get the correct results with the correct reports using the DDR calls and RPCs 
         * available, we are going to use the user specified fromDate but use today as the toDate (like
         * CPRS does) and retrieve all the specimens with in that time period. Then, we will find the
         * reports, match them up with their specimens and lastly filter those results using our original 
         * date range
         */ 
        public ChemHemReport[] getChemHemReports(string dfn, string fromDate, string toDate)
        {
            if (!String.IsNullOrEmpty(toDate) && toDate.IndexOf(".") == -1)
            {
                toDate += ".235959";
            }

            IndexedHashtable specimens = getChemHemSpecimens(dfn, fromDate, toDate, "3");
            if (specimens == null || specimens.Count == 0)
            {
                return null;
            }

            ArrayList lst = new ArrayList();
            //string nextDate = VistaTimestamp.fromUtcString(today);
            string nextDate = VistaTimestamp.fromUtcString(toDate);

            // Due to comment above, we want to loop through "ORWLRR INTERIMG" RPC until we 
            // get as many reports as the DDR call above told us we would
            // TBD - this while statement screams infinite loop (i.e. what happens if we don't get all the 
            // reports the DDR call above said we would?) Should we put an arbitrarily large stop in here?
            // e.g. if count > 1,000,000 then throw an exception that we didn't get what VistA said we should?
            int i = 0;
            while (lst.Count < specimens.Count)
            {
                LabSpecimen specimen = (LabSpecimen)specimens.GetValue(i);
                // ORWLRR INTERIMG rpc and function below updates nextDate for subsequent calls so LEAVE REF ALONE
                string nextDateBefore = nextDate;
                ChemHemReport rpt = getChemHemReport(dfn, ref nextDate);
                if (rpt != null)
                {
                    if ((rpt.Specimen != null) && (rpt.Specimen.AccessionNumber == specimen.AccessionNumber))
                    {
                        i++;
                        rpt.Id = specimen.Id;
                        rpt.Facility = specimen.Facility;
                        rpt.Specimen = specimen;
                        rpt.Timestamp = specimen.ReportDate;
                        lst.Add(rpt);
                    }
                }
                // if the next date variable was not changed below, it means we are no longer looping through 
                // the reports so we should go ahead and break out of this loop - should take care of
                // infinite loop concerns
                if (nextDate.Equals(nextDateBefore))
                {
                    break;
                }
            }
            // At last, filter the reports based on their timestamps
            ArrayList filteredList = new ArrayList();
            double startDate = Convert.ToDouble(fromDate);
            double stopDate = Convert.ToDouble(toDate);
            ChemHemReport[] rpts = (ChemHemReport[])lst.ToArray(typeof(ChemHemReport));
            foreach (ChemHemReport report in rpts)
            {
                // if the report doesn't have a timestamp, we will just add it to our list
                if(String.IsNullOrEmpty(report.Timestamp))
                {
                    filteredList.Add(report);
                    continue;
                }
                double reportTimeStamp = Convert.ToDouble(report.Timestamp);
                if (startDate <= reportTimeStamp && reportTimeStamp <= stopDate)
                {
                    filteredList.Add(report);
                }
            }
            return (ChemHemReport[])filteredList.ToArray(typeof(ChemHemReport));
        }

        internal IndexedHashtable getChemHemSpecimens(string dfn, string fromDate, string toDate, string pieceNum)
        {
            VistaLabsDao labsDao = new VistaLabsDao(cxn);
            string lrdfn = labsDao.getLrDfn(dfn);
            if (lrdfn == "")
            {
                return null;
            }
            DdrLister query = buildGetChemHemSpecimensQuery(lrdfn, fromDate, toDate, pieceNum);
            string[] response = query.execute();
            return toChemHemSpecimens(response, ref fromDate, ref toDate);
        }

        internal DdrLister buildGetChemHemSpecimensQuery(string lrdfn, string fromDate, string toDate, string pieceNum)
        {
            DateUtils.CheckDateRange(fromDate, toDate);
            DdrLister query = new DdrLister(cxn);
            query.File = "63.04";
            query.Iens = "," + lrdfn + ",";

            // E flag note
            // .01 - DATE/TIME SPECIMEN TAKEN
            // .03 - DATE REPORT COMPLETED
            // .05 - SPECIMEN TYPE
            // .06 - ACCESSION
            // .08 - METHOD OR SITE
            // .112 - ACCESSIONING INSTITUTION
            query.Fields = ".01;.03;.05E;.06;.08;.112E";

            query.Flags = "IP";
            query.Xref = "#";
            query.Screen = buildChemHemSpecimensScreenParam(fromDate, toDate, pieceNum);
            query.Id = "S X=$P(^(0),U,14) I X'= \"\" S Y=$P($G(^DIC(4,X,99)),U,1) D EN^DDIOL(Y)";
            return query;
        }

        internal string buildChemHemSpecimensScreenParam(string fromDate, string toDate, string pieceNum)
        {
            if (fromDate == "")
            {
                return "";
            }
            fromDate = VistaTimestamp.fromUtcFromDate(fromDate);
            toDate = VistaTimestamp.fromUtcToDate(toDate);
            return "S FD=" + fromDate + ",TD=" + toDate + ",CDT=$P(^(0),U," + pieceNum + ") I CDT>=FD,CDT<TD";
        }

        internal IndexedHashtable toChemHemSpecimens(string[] response, ref string fromDate, ref string toDate)
        {
            IndexedHashtable result = new IndexedHashtable(response.Length);
            for (int i = 0; i < response.Length; i++)
            {
                string[] flds = StringUtils.split(response[i], StringUtils.CARET);
                LabSpecimen specimen = new LabSpecimen();
                specimen.Id = flds[0];
                specimen.ReportDate = VistaTimestamp.toUtcString(flds[2]);
                specimen.CollectionDate = VistaTimestamp.toUtcString(flds[1]);
                if (i == 0)
                {
                    fromDate = specimen.CollectionDate;
                }
                if (i == response.Length-1)
                {
                    toDate = specimen.CollectionDate;
                }
                specimen.Name = flds[3];
                specimen.AccessionNumber = flds[4];
                if (flds.Length > 6)
                {
                    specimen.Site = flds[5];
                }
                if (flds.Length > 7)
                {
                    specimen.Facility = new SiteId(flds[7], flds[6]);
                }
                string key = flds[1] + '^' + flds[4];
                result.Add(key, specimen);
            }
            return result;
        }

        public ChemHemReport getChemHemReport(string dfn, ref string nextDate)
        {
            MdoQuery request = buildGetChemHemReportRequest(dfn, nextDate);
            string response = (string)cxn.query(request, new MenuOption(VistaConstants.CPRS_CONTEXT));
            return toChemHemReport(response, ref nextDate);
        }

        // Do not check DFN here.  This is called by getChemHemReports which checks DFN.
        // Also, all this will be replaced by the NHIN RPC.
        internal MdoQuery buildGetChemHemReportRequest(string dfn, string nextDate)
        {
            VistaUtils.CheckRpcParams(dfn);

            if (String.IsNullOrEmpty(nextDate) /* || !VistaTimestamp.isValid(nextDate) */)
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Invalid 'next' date: " + nextDate);
            }
            VistaQuery vq = new VistaQuery("ORWLRR INTERIMG");
            vq.addParameter(vq.LITERAL, dfn);
            vq.addParameter(vq.LITERAL, nextDate);
            vq.addParameter(vq.LITERAL, "1");
            vq.addParameter(vq.LITERAL, "1");
            return vq;
        }

        internal ChemHemReport toChemHemReport(string response, ref string nextDate)
        {
            if (String.IsNullOrEmpty(response))
            {
                return null;
            }

            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            string[] flds = StringUtils.split(lines[0], StringUtils.CARET);
            nextDate = flds[2];
            if (flds[1] != "CH")
            {
                return null;
            }
            if (lines.Length == 1)
            {
                return null;
            }
            int ntests = Convert.ToInt16(flds[0]);
            ChemHemReport rpt = new ChemHemReport();
            rpt.Id = flds[2] + '^' + flds[5];
            rpt.Timestamp = flds[2];
            rpt.Specimen = new LabSpecimen();
            rpt.Specimen.CollectionDate = VistaTimestamp.toUtcString(flds[2]);
            rpt.Specimen.Name = flds[4];
            rpt.Specimen.AccessionNumber = flds[5];
            rpt.Author = new Author();
            rpt.Author.Name = flds[6];

            int lineIdx = 1;
            for (lineIdx = 1; lineIdx <= ntests; lineIdx++)
            {
                LabResult lr = new LabResult();
                flds = StringUtils.split(lines[lineIdx], StringUtils.CARET);
                if (flds.Length < 6)
                {
                    continue;
                }
                lr.Test = new LabTest();

                lr.Test.Id = flds[0];
                lr.Test.Name = flds[1];
                lr.Value = flds[2].Trim();
                lr.BoundaryStatus = flds[3];
                lr.Test.Units = flds[4].Trim();
                lr.Test.RefRange = flds[5];

                // MHV patch - probably no one needs this
                lr.LabSiteId = cxn.DataSource.SiteId.Id;

                rpt.AddResult(lr);
            }

            rpt.Comment = "";
            while (lineIdx < lines.Length)
            {
                rpt.Comment += lines[lineIdx++].TrimStart() + "\r\n";
            }

            return rpt;
        }

        //=========================================================================================
        // Chem/Hem RDV
        //=========================================================================================
        public ChemHemReport[] getChemHemReports(string fromDate, string toDate, int nrpts)
        {
            return getChemHemReports(cxn.Pid, fromDate, toDate, nrpts);
        }

        public ChemHemReport[] getChemHemReports(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildChemHemReportsRequest(dfn, fromDate, toDate, nrpts);
            string response = (string)cxn.query(request);
            return toChemHemReports(response);
        }

        internal MdoQuery buildChemHemReportsRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_CH:CHEM & HEMATOLOGY~CH;ORDV02;3;");
        }

        internal ChemHemReport[] toChemHemReports(string response)
        {
            if (response == "")
            {
                return null;
            }
            DictionaryHashList lst = new DictionaryHashList();
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);

            ChemHemReport rpt = null;
            LabResult rslt = null;
            string ts = "";
            string facilityTag = "";
            string facilityName = "";
            for (int i = 0; i < lines.Length; i++)
            {
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                if (!StringUtils.isNumeric(flds[0]))
                {
                    throw new DataMisalignedException("Invalid fldnum: " + flds[0] + " in lines[" + i + "]");
                }
                int fldnum = Convert.ToInt32(flds[0]);
                switch (fldnum)
                {
                    case 1:
                        string[] parts = StringUtils.split(flds[1], StringUtils.SEMICOLON);
                        if (parts.Length == 2)
                        {
                            facilityTag = parts[1];
                            facilityName = parts[0];
                        }
                        else if (flds[1] != "")
                        {
                            facilityTag = cxn.DataSource.SiteId.Id;
                            facilityName = flds[1];
                        }
                        else
                        {
                            facilityTag = cxn.DataSource.SiteId.Id;
                            facilityName = cxn.DataSource.SiteId.Name;
                        }
                        break;
                    case 2:
                        if (rpt != null)
                        {
                            if (StringUtils.isEmpty(rslt.Test.RefRange))
                            {
                                if (!StringUtils.isEmpty(rslt.Test.LowRef) &&
                                    !StringUtils.isEmpty(rslt.Test.HiRef))
                                {
                                    rslt.Test.RefRange = rslt.Test.LowRef + " - " + rslt.Test.HiRef;
                                }
                            }
                            rpt.AddResult(rslt);
                        }
                        rslt = new LabResult();
                        rslt.Test = new LabTest();
                        ts = VistaTimestamp.toUtcFromRdv(flds[1]);
                        if (lst[ts] == null)
                        {
                            rpt = new ChemHemReport();
                            rpt.Facility = new SiteId(facilityTag, facilityName);
                            rpt.Timestamp = ts;
                            lst.Add(ts, rpt);
                        }
                        break;
                    case 3:
                        if (flds.Length == 2)
                        {
                            rslt.Test.Name = flds[1];
                        }
                        break;
                    case 4:
                        if (flds.Length == 2)
                        {
                            if (null != rslt)
                                rslt.SpecimenType = flds[1];
                        }
                        break;
                    case 5:
                        if (flds.Length == 2)
                        {
                            rslt.Value = flds[1];
                        }
                        break;
                    case 6:
                        if (flds.Length == 2)
                        {
                            rslt.BoundaryStatus = flds[1];
                        }
                        break;
                    case 7:
                        if (flds.Length == 2)
                        {
                            rslt.Test.Units = flds[1];
                        }
                        break;
                    case 8:
                        if (flds.Length == 2)
                        {
                            rslt.Test.LowRef = flds[1];
                        }
                        break;
                    case 9:
                        if (flds.Length == 2)
                        {
                            rslt.Test.HiRef = flds[1];
                        }
                        break;
                    case 10:
                        if (flds.Length == 2)
                        {
                            rslt.Comment += flds[1] + '\n';
                        }
                        break;
                }
            }

            if (rpt != null)
            {
                if (StringUtils.isEmpty(rslt.Test.RefRange))
                {
                    if (!StringUtils.isEmpty(rslt.Test.LowRef) &&
                        !StringUtils.isEmpty(rslt.Test.HiRef))
                    {
                        rslt.Test.RefRange = rslt.Test.LowRef + " - " + rslt.Test.HiRef;
                    }
                }
                rpt.AddResult(rslt);
            }
            
            ChemHemReport[] result = new ChemHemReport[lst.Count];
            for (int i = 0; i < lst.Count; i++)
            {
                DictionaryEntry de = lst[i];
                result[i] = (ChemHemReport)de.Value;
            }
            return result;
        }


        public Dictionary<string, HashSet<string>> getNewChemHemReports(DateTime start)
        {
            throw new NotImplementedException();
        }
    }
}
