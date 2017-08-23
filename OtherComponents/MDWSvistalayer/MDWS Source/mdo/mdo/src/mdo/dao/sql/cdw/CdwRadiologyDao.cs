using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.dao;
using System.Data.SqlClient;
using System.Data;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.sql.cdw
{
    public class CdwRadiologyDao : IRadiologyDao
    {
        CdwConnection _cxn;

        public CdwRadiologyDao(AbstractConnection cxn)
        {
            _cxn = cxn as CdwConnection;
        }

        #region Radiology Reports

        public RadiologyReport[] getRadiologyReportsBySite(string fromDate, string toDate, string siteCode)
        {
            SqlDataAdapter adapter = buildRadiologyReportRequest(siteCode, _cxn.Pid, fromDate, toDate);
            IDataReader reader = (IDataReader)_cxn.query(adapter);

            return toRadiologyReports(reader);
        }

        internal SqlDataAdapter buildRadiologyReportRequest(string station, string dfn, string fromDate, string toDate)
        {
            String queryString = 
                "SELECT station__no, station__no+'-'+CONVERT(varchar(20), row_id) as id, patient_name as patientId, patientnamex, report_status, CONVERT(varchar(50), case_number) as caseNumber, "+
                    "exam_datetime as dt, category_of_exam, procedure102, reporttextwp, impressiontextwp "+
                "FROM Radiology.radnuc_med_reports_74 "+
                "WHERE station__no = @station and patient_name = @patientId and exam_datetime > @fromDate and exam_datetime < @toDate";

            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(queryString);

            SqlParameter stationParameter = new SqlParameter("@station", System.Data.SqlDbType.VarChar);
            stationParameter.Value = station;
            adapter.SelectCommand.Parameters.Add(stationParameter);

            SqlParameter patientIdParameter = new SqlParameter("@patientId", System.Data.SqlDbType.Decimal);
            patientIdParameter.Value = dfn;
            adapter.SelectCommand.Parameters.Add(patientIdParameter);

            SqlParameter fromDateParameter = new SqlParameter("@fromDate", System.Data.SqlDbType.Date);
            fromDateParameter.Value = fromDate;
            adapter.SelectCommand.Parameters.Add(fromDateParameter);

            SqlParameter toDateParameter = new SqlParameter("@toDate", System.Data.SqlDbType.Date);
            toDateParameter.Value = toDate;
            adapter.SelectCommand.Parameters.Add(toDateParameter);
            
            return adapter;
        }

        internal RadiologyReport[] toRadiologyReports(IDataReader reader)
        {
            IList<RadiologyReport> reports = new List<RadiologyReport>();

            try
            {
                while (reader.Read())
                {
                    reports.Add(buildRadiologyReport(reader));
                }
            }
            finally
            {
                reader.Close();
            }

            return reports.ToArray();
        }

        internal RadiologyReport buildRadiologyReport(IDataReader reader)
        {
            SiteId facility = new SiteId() {
                Id =  DbReaderUtil.getValue(reader, reader.GetOrdinal("station__no"))
            };

            RadiologyReport report = new RadiologyReport()
            {
                Facility = facility,
                Id = DbReaderUtil.getValue(reader, reader.GetOrdinal("id")),
                Impression = DbReaderUtil.getValue(reader, reader.GetOrdinal("impressiontextwp")),
                Status = DbReaderUtil.getValue(reader, reader.GetOrdinal("report_status")),
                Text = DbReaderUtil.getValue(reader, reader.GetOrdinal("reporttextwp")),
                Title = DbReaderUtil.getValue(reader, reader.GetOrdinal("procedure102")),
                Timestamp = DbReaderUtil.getDateTimeValue(reader, reader.GetOrdinal("dt")),
                CaseNumber = DbReaderUtil.getValue(reader, reader.GetOrdinal("caseNumber"))
            };

            return report;
        }

        

        #endregion

        #region Unimplemented

        public RadiologyReport getImagingReport(string dfn, string accessionNumber)
        {
            throw new NotImplementedException();
        }

        public RadiologyReport[] getRadiologyReports(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        #endregion



        public IList<RadiologyCancellationReason> getCancellationReasons()
        {
            throw new NotImplementedException();
        }

        public IList<ImagingExam> getExamsByPatient(String patientId)
        {
            throw new NotImplementedException();
        }


        public void cancelExam(string examIdentifier, string reasonIen, bool cancelAssociatedOrder = true, string holdDescription = null)
        {
            throw new NotImplementedException();
        }


        public Dictionary<string, string> getContrastMedia()
        {
            throw new NotImplementedException();
        }

        public Dictionary<string, string> getComplications()
        {
            throw new NotImplementedException();
        }

        public ImagingExam registerExam(string orderId, string examDateTime, string examCategory, string hospitalLocation, string ward, string service, string technologistComment)
        {
            throw new NotImplementedException();
        }

        public Order discontinueOrder(string patientId, string orderIen, string providerDuz, string locationIen, string reasonIen)
        {
            throw new NotImplementedException();
        }

        public Order discontinueAndSignOrder(string patientId, string orderIen, string providerDuz, string locationIen, string reasonIen, string eSig)
        {
            throw new NotImplementedException();
        }

        public void signOrder(string orderIen, string providerDuz, string locationIen, string eSig)
        {
            throw new NotImplementedException();
        }
    }
}
