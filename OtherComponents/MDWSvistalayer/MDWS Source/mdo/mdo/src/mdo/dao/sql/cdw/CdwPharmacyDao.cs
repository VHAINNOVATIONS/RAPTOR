using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Data.SqlClient;
using System.Data;
using gov.va.medora.mdo.domain.ccr;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.sql.cdw
{
    public class CdwPharmacyDao : IPharmacyDao
    {
        CdwConnection _cxn;

        public CdwPharmacyDao(AbstractConnection cxn)
        {
            _cxn = (CdwConnection)cxn;
        }

        public Immunizations getImmunizations(string startDate, string stopDate)
        {
            return getImmunizations(_cxn.Pid, startDate, stopDate);
        }

        internal Immunizations getImmunizations(string icn, string startDate, string stopDate)
        {
            SqlDataAdapter adapter = buildGetImmunizationsQuery(icn, startDate, stopDate);
            IDataReader reader = (IDataReader)_cxn.query(adapter);
            return toImmunizations(reader);
        }

        internal SqlDataAdapter buildGetImmunizationsQuery(string icn, string startDate, string stopDate)
        {
            string sql = "";

            if (!String.IsNullOrEmpty(startDate) && !String.IsNullOrEmpty(stopDate))
            {
                sql = "SELECT IMM.ImmunizationSID, IMM.ImmunizationIEN, IMM.Sta3n, IMM.PatientIEN, IMM.PatientSID, IMM.ImmunizationName, IMM.ImmunizationShortName, " +
                    "IMM.ImmunizationInactiveFlag, IMM.Series, IMM.Reaction, IMM.ContraindicatedFlag, IMM.EventDateTime, IMM.VisitVistaDate, IMM.VisitDateTime, " +
                    "IMM.ImmunizationDateTime, IMM.OrderingStaffIEN, IMM.Comments FROM Immun.Immunization IMM JOIN SPatient.SPatient PAT ON " +
                    "IMM.PatientSID=PAT.PatientSID WHERE PAT.PatientICN=@icn AND IMM.ImmunizationDateTime>@startDate AND IMM.ImmunizationDateTime<@stopDate;";
            }
            else
            {
                sql = "SELECT IMM.ImmunizationSID, IMM.ImmunizationIEN, IMM.Sta3n, IMM.PatientIEN, IMM.PatientSID, IMM.ImmunizationName, IMM.ImmunizationShortName, " +
                    "IMM.ImmunizationInactiveFlag, IMM.Series, IMM.Reaction, IMM.ContraindicatedFlag, IMM.EventDateTime, IMM.VisitVistaDate, IMM.VisitDateTime, " +
                    "IMM.ImmunizationDateTime, IMM.OrderingStaffIEN, IMM.Comments FROM Immun.Immunization IMM JOIN SPatient.SPatient PAT ON " +
                    "IMM.PatientSID=PAT.PatientSID WHERE PAT.PatientICN=@icn;";
            }


            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(sql);

            SqlParameter patientIdParam = new SqlParameter("@icn", System.Data.SqlDbType.VarChar, 50);
            patientIdParam.Value = icn;
            adapter.SelectCommand.Parameters.Add(patientIdParam);

            if (!String.IsNullOrEmpty(startDate) && !String.IsNullOrEmpty(stopDate))
            {
                SqlParameter startDateParam = new SqlParameter("@startDate", System.Data.SqlDbType.SmallDateTime);
                startDateParam.Value = startDate;
                adapter.SelectCommand.Parameters.Add(startDateParam);

                SqlParameter stopDateParam = new SqlParameter("@stopDate", System.Data.SqlDbType.SmallDateTime);
                stopDateParam.Value = stopDate;
                adapter.SelectCommand.Parameters.Add(stopDateParam);
            }

            adapter.SelectCommand.CommandTimeout = 600; // allow query to run for up to 10 minutes
            return adapter;

        }

        internal Immunizations toImmunizations(IDataReader reader)
        {
            Immunizations immunizations = new Immunizations();
            immunizations.Immunization = new List<StructuredProductType>();
            StructuredProductType current = new StructuredProductType();

            while (reader.Read())
            {
                Int32 immSid = reader.GetInt32(reader.GetOrdinal("ImmunizationSID"));
                string immIen = reader.GetString(reader.GetOrdinal("ImmunizationIEN"));
                Int16 sitecode = reader.GetInt16(reader.GetOrdinal("Sta3n"));
                string patientIen = getValue(reader, reader.GetOrdinal("PatientIEN"));
                //string patientSid = getValue(reader, reader.GetOrdinal("PatientSID"));
                string immunizationName = getValue(reader, reader.GetOrdinal("ImmunizationName"));
                string immunizationShortName = getValue(reader, reader.GetOrdinal("ImmunizationShortName"));
                string inactive = getValue(reader, reader.GetOrdinal("ImmunizationInactiveFlag"));
                string series = getValue(reader, reader.GetOrdinal("Series"));
                string reaction = getValue(reader, reader.GetOrdinal("Reaction"));
                string contraindicated = getValue(reader, reader.GetOrdinal("ContraindicatedFlag"));
                DateTime eventDateTime = reader.IsDBNull(reader.GetOrdinal("EventDateTime")) ? new DateTime() : reader.GetDateTime(reader.GetOrdinal("EventDateTime"));
                DateTime visitDateTime = reader.IsDBNull(reader.GetOrdinal("VisitDateTime")) ? new DateTime() : reader.GetDateTime(reader.GetOrdinal("VisitDateTime"));
                DateTime immunizationDateTime = reader.IsDBNull(reader.GetOrdinal("ImmunizationDateTime")) ? new DateTime() : reader.GetDateTime(reader.GetOrdinal("ImmunizationDateTime"));
                string orderedBy = getValue(reader, reader.GetOrdinal("OrderingStaffIEN"));
                string comments = getValue(reader, reader.GetOrdinal("Comments"));

                current.CCRDataObjectID = immSid.ToString();

                current.CommentID = new List<string>();
                current.CommentID.Add(comments);
                
                DateTimeType ts = new DateTimeType() { ExactDateTime = eventDateTime.ToShortDateString(), Type = new CodedDescriptionType() { Text = "EventDateTime" } };
                (current.DateTime = new List<DateTimeType>()).Add(ts);
                
                (current.Product = new List<StructuredProductTypeProduct>()).Add(new StructuredProductTypeProduct() { ProductName = new CodedDescriptionType() { Text = immunizationName } });
                current.Reaction = new Reaction() { Description = new CodedDescriptionType() { Text = reaction } };
                current.SeriesNumber = series;
                
                ActorReferenceType orderingProvider = new ActorReferenceType() { ActorID = orderedBy }; 
                (current.Source = new List<SourceType>()).Add(new SourceType() { Actor = new List<ActorReferenceType>() }); 
                current.Source[0].Actor.Add(orderingProvider);
                
                SourceType site = new SourceType() { ReferenceID = new List<string>() };
                site.ReferenceID.Add(sitecode.ToString());
                current.Source.Add(site);

                current.Type = new CodedDescriptionType() { Text = "Immunization" };

                immunizations.Immunization.Add(current);
                current = new StructuredProductType();
            }

            return immunizations;
        }

        public Medication[] getAllMeds()
        {
            return getAllMeds(_cxn.Pid);
        }

        internal SqlDataAdapter buildGetAllMedsRequest(string icn)
        {
            string storedProcedure = "App.MedicationsList";

            SqlDataAdapter adapter = _cxn.createAdapterForStoredProcedure(storedProcedure);
            adapter.SelectCommand.Parameters.Add("PatientICN", SqlDbType.VarChar).Value = icn;

            return adapter;
        }

        internal Medication[] toMeds(IDataReader reader)
        {
            IList<Medication> results = new List<Medication>();
            while (reader.Read())
            {
                string facility = DbReaderUtil.getValue(reader, reader.GetOrdinal("Facility"));
                string sta3n = DbReaderUtil.getInt16Value(reader, reader.GetOrdinal("Sta3n"));
                string id = DbReaderUtil.getInt64Value(reader, reader.GetOrdinal("RxOutpatSID"));
                string status = DbReaderUtil.getValue(reader, reader.GetOrdinal("RxStatus"));
                string drugNameWithDose = DbReaderUtil.getValue(reader, reader.GetOrdinal("LocalDrugNameWithDose"));
                string issueDate = DbReaderUtil.getDateValue(reader, reader.GetOrdinal("IssueDate"));
                string rxNumber = DbReaderUtil.getValue(reader, reader.GetOrdinal("RxNumber"));
                string refills = DbReaderUtil.getInt16Value(reader, reader.GetOrdinal("MaxRefills"));
                string patientIcn = DbReaderUtil.getValue(reader, reader.GetOrdinal("PatientICN"));
                string sig = DbReaderUtil.getValue(reader, reader.GetOrdinal("Sig"));
                string daysSupply = DbReaderUtil.getInt32Value(reader, reader.GetOrdinal("DaysSupply"));
                string quantity = DbReaderUtil.getValue(reader, reader.GetOrdinal("Qty"));
                string lastFilledDate = DbReaderUtil.getDateValue(reader, reader.GetOrdinal("LastFillDate"));
                string expriationDate = DbReaderUtil.getDateValue(reader, reader.GetOrdinal("ExpirationDate"));

                SiteId site = new SiteId()
                {
                    Name = facility,
                    Id = sta3n
                };
                
                Medication med = new Medication()
                {
                    Facility = site,
                    Id = id,
                    Drug = new KeyValuePair<string, string>(id, drugNameWithDose),
                    Name = drugNameWithDose,
                    RxNumber = rxNumber,
                    IssueDate = issueDate,
                    LastFillDate = lastFilledDate,
                    Refills = refills,
                    Status = status,
                    Sig = sig,
                    ExpirationDate = expriationDate,
                    Quantity = quantity,
                    DaysSupply = daysSupply
                };

                results.Add(med);
            }
            return results.ToArray<Medication>();
        }

        public Medication[] getAllMeds(string icn)
        {
            SqlDataAdapter adapter = buildGetAllMedsRequest(icn);
            IDataReader reader = (IDataReader)_cxn.query(adapter);
            return toMeds(reader);
        }

        bool isDbNull(IDataReader reader, int index)
        {
            return reader[index] == DBNull.Value;
        }

        string getValue(IDataReader reader, int index)
        {
            return (reader[index] == DBNull.Value) ? "" : (string)reader[index];
        }

        #region Not Implemented

        public Medication[] getOutpatientMeds()
        {
            throw new NotImplementedException();
        }

        public Medication[] getIvMeds()
        {
            throw new NotImplementedException();
        }

        public Medication[] getIvMeds(string pid)
        {
            throw new NotImplementedException();
        }

        public Medication[] getUnitDoseMeds()
        {
            throw new NotImplementedException();
        }

        public Medication[] getUnitDoseMeds(string pid)
        {
            throw new NotImplementedException();
        }

        public Medication[] getOtherMeds()
        {
            throw new NotImplementedException();
        }

        public Medication[] getOtherMeds(string pid)
        {
            throw new NotImplementedException();
        }

        public Medication[] getVaMeds(string dfn)
        {
            throw new NotImplementedException();
        }

        public Medication[] getVaMeds()
        {
            throw new NotImplementedException();
        }

        public Medication[] getInpatientForOutpatientMeds()
        {
            throw new NotImplementedException();
        }

        public Medication[] getInpatientForOutpatientMeds(string pid)
        {
            throw new NotImplementedException();
        }

        public string getMedicationDetail(string medId)
        {
            throw new NotImplementedException();
        }

        public string getOutpatientRxProfile()
        {
            throw new NotImplementedException();
        }

        public string getMedsAdminHx(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getMedsAdminLog(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getImmunizations(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }


        public Medication refillPrescription(string rxId)
        {
            throw new NotImplementedException();
        }

        #endregion

    }
}
