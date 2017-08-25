using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;
using gov.va.medora.mdo.dao.hl7.mpi.messages;
using gov.va.medora.mdo.dao.hl7.mpi;
using gov.va.medora.mdo.dao.hl7.components;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.hl7.mpi
{
    public class MpiPatientDao
    {
        MpiConnection cxn = null;

        public MpiPatientDao(AbstractConnection cxn)
        {
            this.cxn = (MpiConnection)cxn;
        }

        public Patient[] match(Patient patient)
        {
            //if (!patient.SSN.isValid())
            //{
            //    throw new Exception("Invalid SSN");
            //}
            //need test for valid name?

            PatientMatchesResponse hl7 = matchPatients(patient);
            Patient result = hl7.getPatient(0);
            if (result == null)
            {
                return null;
            }
            return new Patient[] { result };
        }

        public PatientMatchesResponse matchPatients(Patient patient)
        {
            string request = buildMatchPatientsRequest(patient);
            string response = (string)cxn.query(request);
            string hl7 = extractHL7Message(response);
            return new PatientMatchesResponse(hl7);
        }

        internal string buildMatchPatientsRequest(Patient patient)
        {
            if (patient.Name == null || patient.SSN == null)
            {
                throw new Exception("Must have last name and SSN");
            }

            PatientMatchesRequest msg = new PatientMatchesRequest();

            msg.MSH.SendingApplication = MpiConstants.VQQ_SENDING_APP;
            msg.MSH.SendingFacility = MpiConstants.VQQ_SENDING_FACILITY;
            msg.MSH.ReceivingApplication = MpiConstants.VQQ_RECEIVING_APP;
            msg.MSH.ReceivingFacility = MpiConstants.VQQ_RECEIVING_FACILITY;
            msg.MSH.MessageCode = MpiConstants.VQQ_MSG_CODE;
            msg.MSH.EventTrigger = MpiConstants.VQQ_TRIGGER;
            msg.MSH.MessageControlID = MpiConstants.VQQ_MSG_CTL;
            msg.MSH.ProcessingID = MpiConstants.VQQ_PROCESSING_ID;
            msg.MSH.VersionID = MpiConstants.VQQ_VERSION_ID;
            msg.MSH.AcceptAckType = MpiConstants.VQQ_ACCEPT_ACK_TYPE;
            msg.MSH.ApplicationAckType = MpiConstants.VQQ_APP_ACK_TYPE;
            msg.MSH.CountryCode = MpiConstants.VQQ_COUNTRY_CODE;

            msg.VTQ.QueryTag = MpiConstants.VQQ_QUERY_TAG;
            msg.VTQ.FormatCode = MpiConstants.VQQ_FORMAT_CODE;
            msg.VTQ.QueryName = MpiConstants.VQQ_QUERY_NAME_FUZZY;
            msg.VTQ.VirtualTableName = MpiConstants.VQQ_VIRTUAL_TABLE;
            msg.VTQ.SelectionCriteria = new ArrayList();
            msg.VTQ.SelectionCriteria.Add(
                new SelectionCriterion(MpiConstants.FLD_SSN.FieldName, "EQ", patient.SSN.toString(), "OR"));
            msg.VTQ.SelectionCriteria.Add(
                new SelectionCriterion(MpiConstants.FLD_LASTNAME.FieldName, "EQ", patient.Name.Lastname, "AND"));
            msg.VTQ.SelectionCriteria.Add(
                new SelectionCriterion(MpiConstants.FLD_FIRSTNAME.FieldName, "EQ", patient.Name.Firstname, "AND"));
            msg.VTQ.SelectionCriteria.Add(
                new SelectionCriterion(MpiConstants.FLD_DOB.FieldName, "EQ", patient.DOB, "AND"));
            msg.VTQ.SelectionCriteria.Add(
                new SelectionCriterion(MpiConstants.FLD_SEX.FieldName, "EQ", patient.Gender, ""));

            msg.RDF.NColumns = 12;
            msg.RDF.Columns = new RdfColumn[12];
            msg.RDF.Columns[0] = new RdfColumn(MpiConstants.FLD_ICN);
            msg.RDF.Columns[1] = new RdfColumn(MpiConstants.FLD_SSN);
            msg.RDF.Columns[2] = new RdfColumn(MpiConstants.FLD_LASTNAME);
            msg.RDF.Columns[3] = new RdfColumn(MpiConstants.FLD_FIRSTNAME);
            msg.RDF.Columns[4] = new RdfColumn(MpiConstants.FLD_MIDDLENAME);
            msg.RDF.Columns[5] = new RdfColumn(MpiConstants.FLD_NAME_SUFFIX);
            msg.RDF.Columns[6] = new RdfColumn(MpiConstants.FLD_SSN);
            msg.RDF.Columns[7] = new RdfColumn(MpiConstants.FLD_DOB);
            msg.RDF.Columns[8] = new RdfColumn(MpiConstants.FLD_SEX);
            msg.RDF.Columns[9] = new RdfColumn(MpiConstants.FLD_DECEASED_DATE);
            msg.RDF.Columns[10] = new RdfColumn(MpiConstants.FLD_CMOR);
            msg.RDF.Columns[11] = new RdfColumn(MpiConstants.FLD_SITES);

            return msg.toMessage();
        }

        public Patient[] match(string ssn)
        {
            string request = buildMatchSsnRequest(ssn);
            string response = (string)cxn.query(request);
            string rawMsg = extractHL7Message(response);
            PatientMatchesResponse hl7 = new PatientMatchesResponse(rawMsg);
            return hl7.getPatientsForSSN(ssn);
        }

        internal string buildMatchSsnRequest(string ssn)
        {
            if (!SocSecNum.isValid(ssn))
            {
                throw new Exception("Invalid SSN");
            }
            PatientMatchesRequest hl7 = getBlankHl7Request();
            hl7.VTQ.QueryName = MpiConstants.VQQ_QUERY_NAME_FUZZY;
            hl7.VTQ.SelectionCriteria = new ArrayList(2);
            hl7.VTQ.SelectionCriteria.Add(
                new SelectionCriterion(MpiConstants.FLD_SSN.FieldName, "EQ", ssn, "OR"));
            hl7.VTQ.SelectionCriteria.Add(
                new SelectionCriterion(MpiConstants.FLD_LASTNAME.FieldName, "EQ", "", ""));
            return hl7.toMessage();
        }

        internal PatientMatchesRequest getBlankHl7Request()
        {
            PatientMatchesRequest result = new PatientMatchesRequest();

            result.MSH.SendingApplication = MpiConstants.VQQ_SENDING_APP;
            result.MSH.SendingFacility = MpiConstants.VQQ_SENDING_FACILITY;
            result.MSH.ReceivingApplication = MpiConstants.VQQ_RECEIVING_APP;
            result.MSH.ReceivingFacility = MpiConstants.VQQ_RECEIVING_FACILITY;
            result.MSH.MessageCode = MpiConstants.VQQ_MSG_CODE;
            result.MSH.EventTrigger = MpiConstants.VQQ_TRIGGER;
            result.MSH.MessageControlID = DateTime.Now.ToString("yyyyMMddHHmmss");
            result.MSH.ProcessingID = MpiConstants.VQQ_PROCESSING_ID;
            result.MSH.VersionID = MpiConstants.VQQ_VERSION_ID;
            result.MSH.AcceptAckType = MpiConstants.VQQ_ACCEPT_ACK_TYPE;
            result.MSH.ApplicationAckType = MpiConstants.VQQ_APP_ACK_TYPE;
            result.MSH.CountryCode = MpiConstants.VQQ_COUNTRY_CODE;

            result.VTQ.QueryTag = MpiConstants.VQQ_QUERY_TAG;
            result.VTQ.FormatCode = MpiConstants.VQQ_FORMAT_CODE;
            result.VTQ.VirtualTableName = MpiConstants.VQQ_VIRTUAL_TABLE;

            result.RDF.NColumns = 12;
            result.RDF.Columns = new RdfColumn[12];
            result.RDF.Columns[0] = new RdfColumn(MpiConstants.FLD_ICN);
            result.RDF.Columns[1] = new RdfColumn(MpiConstants.FLD_SSN);
            result.RDF.Columns[2] = new RdfColumn(MpiConstants.FLD_LASTNAME);
            result.RDF.Columns[3] = new RdfColumn(MpiConstants.FLD_FIRSTNAME);
            result.RDF.Columns[4] = new RdfColumn(MpiConstants.FLD_MIDDLENAME);
            result.RDF.Columns[5] = new RdfColumn(MpiConstants.FLD_NAME_SUFFIX);
            result.RDF.Columns[6] = new RdfColumn(MpiConstants.FLD_SSN);
            result.RDF.Columns[7] = new RdfColumn(MpiConstants.FLD_DOB);
            result.RDF.Columns[8] = new RdfColumn(MpiConstants.FLD_SEX);
            result.RDF.Columns[9] = new RdfColumn(MpiConstants.FLD_DECEASED_DATE);
            result.RDF.Columns[10] = new RdfColumn(MpiConstants.FLD_CMOR);
            result.RDF.Columns[11] = new RdfColumn(MpiConstants.FLD_SITES);

            return result;
        }

        internal string extractHL7Message(string response)
        {
            if (!response.StartsWith("DATA PARAM=MPI"))
            {
                throw new Exception("Unexpected response: " + response);
            }
            string[] parts = StringUtils.split(response, StringUtils.CRLF);
            string msg = parts[1];

            string mshSeg = getSegment(msg, "MSH");
            string msaSeg = getSegment(msg, "MSA");
            string qakSeg = getSegment(msg, "QAK");
            string rdfSeg = getSegment(msg, "RDF");
            rdfSeg = rdfSeg.Substring(0, rdfSeg.Length - 1);    // Lose the \r at the end

            string result = mshSeg + '\r' + msaSeg + '\r' + qakSeg + '\r' + rdfSeg;

            int startIdx = msg.IndexOf("RDT");
            while (startIdx != -1)
            {
                string rdtSeg = getRdt(msg, startIdx);
                result += '\r' + rdtSeg;
                startIdx = msg.IndexOf("RDT", startIdx + 3);
            }
            return result;
        }

        internal string getSegment(string response, string hdr)
        {
            int idx = response.IndexOf(hdr);
            if (idx == -1)
            {
                return "";
            }
            int cnt = Convert.ToInt16(response.Substring(idx - 3, 3));
            return response.Substring(idx, cnt);
        }

        internal string getRdt(string response, int startIdx)
        {
            int idx = response.IndexOf("RDT", startIdx);
            if (idx == -1)
            {
                return "";
            }
            int cnt = Convert.ToInt16(response.Substring(idx - 3, 3));
            return response.Substring(idx, cnt);
        }

    }
}
