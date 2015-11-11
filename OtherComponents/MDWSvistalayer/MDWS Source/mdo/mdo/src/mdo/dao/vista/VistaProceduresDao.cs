using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.src.mdo.dao.vista;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaProceduresDao : IProceduresDao
    {
        VistaConnection _cxn;

        public VistaProceduresDao(AbstractConnection cxn)
        {
            _cxn = (VistaConnection)cxn;
        }

        public string getProcedureReport(string noteId)
        {
            MdoQuery request = buildGetClinicalProcedureReportQuery(_cxn.Pid, noteId);
            string response = (string)_cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetClinicalProcedureReportQuery(string pid, string ien)
        {
            VistaQuery vq = new VistaQuery("ORWRP REPORT TEXT");
            vq.addParameter(vq.LITERAL, pid);
            vq.addParameter(vq.LITERAL, "19:PROCEDURES (LOCAL ONLY)~");
            vq.addParameter(vq.LITERAL, "");
            vq.addParameter(vq.LITERAL, "");
            vq.addParameter(vq.LITERAL, ien);
            vq.addParameter(vq.LITERAL, "0");
            vq.addParameter(vq.LITERAL, "0");
            return vq;
        }

        void supplementText(IList<ClinicalProcedure> proceduresToSupplement)
        {
            foreach (ClinicalProcedure procedure in proceduresToSupplement)
            {
                procedure.Report = getProcedureReport(procedure.ReportIen);
            }
        }

        public IList<ClinicalProcedure> getClinicalProceduresWithText(IList<string> filter)
        {
            IList<ClinicalProcedure> result = getClinicalProcedures(filter);
            supplementText(result);
            return result;
        }

        public IList<ClinicalProcedure> getClinicalProceduresWithText()
        {
            IList<ClinicalProcedure> result = getClinicalProcedures();
            supplementText(result);
            return result;
        }


        public IList<ClinicalProcedure> getClinicalProcedures(IList<string> filter)
        {
            IList<ClinicalProcedure> unfiltered = getClinicalProcedures();
            if (filter == null || filter.Count == 0)
            {
                return unfiltered;
            }
            IList<ClinicalProcedure> filtered = new List<ClinicalProcedure>();
            foreach (ClinicalProcedure cp in unfiltered)
            {
                if (String.IsNullOrEmpty(cp.Name))
                {
                    continue;
                }
                if (filter.Contains(cp.Name, System.StringComparer.CurrentCultureIgnoreCase))
                {
                    filtered.Add(cp);
                }
            }
            return filtered;
        }

        public IList<ClinicalProcedure> getClinicalProcedures()
        {
            MdoQuery request = buildGetClinicalProceduresQuery1(_cxn.Pid);
            string response = (string)_cxn.query(request);
            return toClinicalProcedures(response);
        }

        internal MdoQuery buildGetClinicalProceduresQuery1(string pid)
        {
            VistaQuery query = new VistaQuery("ORWMC PATIENT PROCEDURES1");
            query.addParameter(query.LITERAL, pid);
            return query;
        }

        //    0       1    2      3    4    5    6         7             8           911  12   13  14      15
        //SAGINAW;655^1^CP ECHO^7586^PR702^MDPS1^^NOV 15,2011@10:15^MACHINE RESULTED^^^^CP ECHO^^864314^12511132
        // notes - some procedure records have fewer fields. the result length appears to correspond to the value
        // in the 5 piece above. i'm sure there is some business rule there about what type of procedure or report
        // each record has. for now, should be ok since we make sure we have the id before fetching supplementary
        // note text. in the future, there may be other RPCs that can be invoked based on the other identifiers
        // that will return an alternate report type. maybe...
        internal IList<ClinicalProcedure> toClinicalProcedures(string response)
        {
            IList<ClinicalProcedure> results = new List<ClinicalProcedure>();

            if (String.IsNullOrEmpty(response))
            {
                return results;
            }

            string[] records = StringUtils.split(response, StringUtils.CRLF);

            if (records == null || records.Length <= 0)
            {
                return results;
            }

            for (int i = 0; i < records.Length; i++)
            {
                if (String.IsNullOrEmpty(records[i]))
                {
                    continue;
                }

                ClinicalProcedure cp = new ClinicalProcedure();

                string[] pieces = StringUtils.split(records[i], StringUtils.CARET);
                if (pieces.Length >= 1)
                {
                    string[] siteFields = StringUtils.split(pieces[0], StringUtils.SEMICOLON);
                    cp.Facility = new Site(siteFields[1], siteFields[0]);
                }
                if (pieces.Length > 1)
                {
                    cp.ReportIen = pieces[1];
                }
                if (pieces.Length > 2)
                {
                    cp.Name = pieces[2];
                }
                if (pieces.Length > 3)
                {
                    cp.Id = pieces[3];
                }
                if (pieces.Length > 7)
                {
                    cp.Timestamp = pieces[7];
                }
                if (pieces.Length > 15)
                {
                    cp.Note = new Note();
                    cp.Note.Id = pieces[15];
                }

                results.Add(cp);
            }

            return results;
        }
    }
}
