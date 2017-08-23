using System;
using System.Collections;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo.dao.hl7.segments;
using gov.va.medora.mdo.dao.hl7.components;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.hl7.mpi.messages
{
    public class PatientMatchesResponse
    {
        EncodingCharacters encChars;
        MshSegment msh;
        MsaSegment msa;
        QakSegment qak;
        RdfSegment rdf;
        RdtSegment[] rdt;

        public PatientMatchesResponse() { }

        public PatientMatchesResponse(string rawMessage)
        {
            parse(rawMessage);
        }

        public EncodingCharacters EncodingChars
        {
            get { return encChars; }
            set { encChars = value; }
        }

        public MshSegment MSH
        {
            get { return msh; }
            set { msh = value; }
        }

        public MsaSegment MSA
        {
            get { return msa; }
            set { msa = value; }
        }

        public QakSegment QAK
        {
            get { return qak; }
            set { qak = value; }
        }

        public RdfSegment RDF
        {
            get { return rdf; }
            set { rdf = value; }
        }

        public RdtSegment[] RDT
        {
            get { return rdt; }
            set { rdt = value; }
        }

        public Patient getPatient(int rdtIdx)
        {
            if (RDT == null)
            {
                return null;
            }
            if (QAK.QueryResponseStatus != "OK")
            {
                return null;
            }
            if (rdtIdx > RDT.Length)
            {
                throw new IndexOutOfRangeException("Only " + RDT.Length + " RDTs");
            }
            Patient result = new Patient();

            RdtColumn column = RDT[rdtIdx].getColumn(MpiConstants.LASTNAME_FLDNAME);
            string patname = column.Values[0];
            column = RDT[rdtIdx].getColumn(MpiConstants.FIRSTNAME_FLDNAME);
            if (column.Values != null && !StringUtils.isEmpty(column.Values[0]))
            {
                patname += ',' + column.Values[0];
            }
            column = RDT[rdtIdx].getColumn(MpiConstants.MIDDLENAME_FLDNAME);
            if (column.Values != null && !StringUtils.isEmpty(column.Values[0]))
            {
                patname += ' ' + column.Values[0];
            }
            column = RDT[rdtIdx].getColumn(MpiConstants.NAMESUFFIX_FLDNAME);
            if (column.Values != null && !StringUtils.isEmpty(column.Values[0]))
            {
                patname += ' ' + column.Values[0];
            }
            result.Name = new PersonName(patname);

            column = RDT[rdtIdx].getColumn(MpiConstants.SSN_FLDNAME);
            result.SSN = new SocSecNum(column.Values[0]);

            column = RDT[rdtIdx].getColumn(MpiConstants.ICN_FLDNAME);
            string[] parts = StringUtils.split(column.Values[0], 'V');
            result.MpiPid = parts[0];
            result.MpiChecksum = parts[1];

            column = RDT[rdtIdx].getColumn(MpiConstants.DOB_FLDNAME);
            if (column.Values != null && !StringUtils.isEmpty(column.Values[0]))
            {
                result.DOB = column.Values[0];
            }

            column = RDT[rdtIdx].getColumn(MpiConstants.SEX_FLDNAME);
            if (column != null && column.Values != null)
            {
                result.Gender = column.Values[0];
            }

            column = RDT[rdtIdx].getColumn(MpiConstants.DECEASEDDATE_FLDNAME);
            if (column.Values != null && !StringUtils.isEmpty(column.Values[0]))
            {
                result.DeceasedDate = column.Values[0];
            }

            column = RDT[rdtIdx].getColumn(MpiConstants.SITES_FLDNAME);
            if (column != null && column.Values != null && column.Values.Length > 0)
            {
                ArrayList lst = new ArrayList(column.Values.Length);
                for (int i = 0; i < column.Values.Length; i++)
                {
                    parts = StringUtils.split(column.Values[i], StringUtils.CARET);
                    if (parts[0].Length == 3 && StringUtils.isNumeric(parts[0]) && parts[0] != "003")
                    {
                        SiteId s = new SiteId(parts[0],"","","");
                        if (parts.Length > 2)
                        {
                            s = new SiteId(parts[0], "", parts[1], parts[2]);
                        }
                        lst.Add(s);
                    }
                }
                result.SiteIDs = (SiteId[])lst.ToArray(typeof(SiteId));
            }

            column = RDT[rdtIdx].getColumn(MpiConstants.CMOR_FLDNAME);
            if (column != null && !StringUtils.isEmpty(column.Values[0]))
            {
                result.CmorSiteId = column.Values[0];
            }

            return result;
        }

        public Patient[] getPatients()
        {
            if (RDT == null || RDT.Length == 0)
            {
                return null;
            }
            Patient[] result = new Patient[RDT.Length];
            for (int i = 0; i < RDT.Length; i++)
            {
                result[i] = getPatient(i);
            }
            return result;
        }

        public Patient[] getPatientsForSSN(string ssn)
        {
            if (RDT == null || RDT.Length == 0)
            {
                return null;
            }
            ArrayList lst = new ArrayList(RDT.Length);
            for (int i = 0; i < RDT.Length; i++)
            {
                Patient p = getPatient(i);
                if (p.SSN.toString() == ssn)
                {
                    lst.Add(p);
                }
            }
            return (Patient[])lst.ToArray(typeof(Patient));
        }

        internal void parse(string rawMessage)
        {
            string[] segments = StringUtils.split(rawMessage, '\r');
            if (segments.Length < 4)
            {
                throw new Exception("Invalid message: needs 5 segments");
            }
            MSH = new MshSegment(segments[0]);
            MSA = new MsaSegment(segments[1]);
            QAK = new QakSegment(segments[2]);
            RDF = new RdfSegment(segments[3]);
            if (segments.Length > 4)
            {
                RDT = new RdtSegment[segments.Length - 4];
                for (int rdtIdx = 0, segIdx = 4; segIdx < segments.Length; rdtIdx++, segIdx++)
                {
                    RDT[rdtIdx] = new RdtSegment(RDF, segments[segIdx]);
                }
            }
        }
    }
}
