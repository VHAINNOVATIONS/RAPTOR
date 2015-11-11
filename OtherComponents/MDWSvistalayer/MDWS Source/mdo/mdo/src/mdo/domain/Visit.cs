using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Visit
    {
        SiteId _facility;
        string id;
        string type;
        Patient patient;
        User attending;
        User provider;
        string service;
        HospitalLocation location;
        string patientType;
        string visitId;
        string timestamp;
        string status;

        public Visit() {}

        public string Id
        {
            get
            {
                return id;
            }
            set
            {
                id = value;
            }
        }

        public SiteId Facility
        {
            get { return _facility; }
            set { _facility = value; }
        }

        public string Type
        {
            get { return type; }
            set { type = value; }
        }

        public Patient Patient
        {
            get
            {
                return patient;
            }
            set
            {
                patient = value;
            }
        }

        public User Attending
        {
            get
            {
                return attending;
            }
            set
            {
                attending = value;
            }
        }

        public User Provider
        {
            get
            {
                return provider;
            }
            set
            {
                provider = value;
            }
        }

        public string Service
        {
            get
            {
                return service;
            }
            set
            {
                service = value;
            }
        }

        public HospitalLocation Location
        {
            get
            {
                return location;
            }
            set
            {
                location = value;
            }
        }

        public string PatientType
        {
            get
            {
                return patientType;
            }
            set
            {
                patientType = value;
            }
        }

        public string VisitId
        {
            get
            {
                return visitId;
            }
            set
            {
                visitId = value;
            }
        }

        public string Timestamp
        {
            get
            {
                return timestamp;
            }
            set
            {
                timestamp = value;
            }
        }

        public string Status
        {
            get { return status; }
            set { status = value; }
        }
    }
}
