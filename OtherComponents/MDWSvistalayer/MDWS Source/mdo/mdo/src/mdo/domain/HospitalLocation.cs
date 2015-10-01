using System;
using System.Collections.Generic;
using System.Collections;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo
{
    public class HospitalLocation
    {
        string id;
        string name;
        string abbr;
        string type;
        KeyValuePair<string, string> typeExtension;
        KeyValuePair<string, string> institution;
        KeyValuePair<string, string> division;
        KeyValuePair<string, string> module;
        string dispositionAction;
        string visitLocation;
        KeyValuePair<string, string> stopCode;
        KeyValuePair<string, string> department;
        KeyValuePair<string, string> service;
        KeyValuePair<string, string> specialty;
        string physicalLocation;
        KeyValuePair<string, string> wardLocation;
        KeyValuePair<string, string> principalClinic;
        Site facility;
        string building;
        string floor;
        string room;
        string bed;
        string status;
        string phone;
        string appointmentTimestamp;
        bool _askForCheckIn; // file 44 field 24
        string _appointmentLength; // file 44 field 1912
        string _clinicDisplayStartTime; // file 44, field 1914
        string _displayIncrements; // file 44, field 1917
        IList<TimeSlot> _availability;

        const string DAO_NAME = "ILocationDao";

        public IList<TimeSlot> Availability
        {
            get { return _availability; }
            set { _availability = value; }
        }

        public string ClinicDisplayStartTime
        {
            get { return _clinicDisplayStartTime; }
            set { _clinicDisplayStartTime = value; }
        }

        public string DisplayIncrements
        {
            get { return _displayIncrements; }
            set { _displayIncrements = value; }
        }

        public string AppointmentLength
        {
            get { return _appointmentLength; }
            set { _appointmentLength = value; }
        }

        public bool AskForCheckIn
        {
            get { return _askForCheckIn; }
            set { _askForCheckIn = value; }
        }

        public HospitalLocation(string id, string name) 
        {
            Id = id;
            Name = name;
        }

        public HospitalLocation() {}

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public string Name
        {
            get { return name; }
            set { name = value; }
        }

        public string Abbr
        {
            get { return abbr; }
            set { abbr = value; }
        }

        public string Type
        {
            get { return type; }
            set { type = value; }
        }

        public KeyValuePair<string, string> TypeExtension
        {
            get { return typeExtension; }
            set { typeExtension = value; }
        }

        public KeyValuePair<string, string> Institution
        {
            get { return institution; }
            set { institution = value; }
        }

        public KeyValuePair<string, string> Division
        {
            get { return division; }
            set { division = value; }
        }

        public KeyValuePair<string, string> Module
        {
            get { return module; }
            set { module = value; }
        }

        public string DispositionAction
        {
            get { return dispositionAction; }
            set { dispositionAction = value; }
        }

        public string VisitLocation
        {
            get { return visitLocation; }
            set { visitLocation = value; }
        }

        public KeyValuePair<string, string> StopCode
        {
            get { return stopCode; }
            set { stopCode = value; }
        }

        public KeyValuePair<string, string> Department
        {
            get { return department; }
            set { department = value; }
        }

        public KeyValuePair<string, string> Service
        {
            get { return service; }
            set { service = value; }
        }

        public KeyValuePair<string, string> Specialty
        {
            get { return specialty; }
            set { specialty = value; }
        }

        public string PhysicalLocation
        {
            get { return physicalLocation; }
            set { physicalLocation = value; }
        }

        public KeyValuePair<string, string> WardLocation
        {
            get { return wardLocation; }
            set { wardLocation = value; }
        }

        public KeyValuePair<string, string> PrincipalClinic
        {
            get { return principalClinic; }
            set { principalClinic = value; }
        }

        public Site Facility
        {
            get { return facility; }
            set { facility = value; }
        }

        public string Building
        {
            get { return building; }
            set { building = value; }
        }

        public string Floor
        {
            get { return floor; }
            set { floor = value; }
        }

        public string Room
        {
            get { return room; }
            set { room = value; }
        }

        public string Bed
        {
            get { return bed; }
            set { bed = value; }
        }

        public string Status
        {
            get { return status; }
            set { status = value; }
        }

        public string Phone
        {
            get { return phone; }
            set { phone = value; }
        }

        public string AppointmentTimestamp
        {
            get { return appointmentTimestamp; }
            set { appointmentTimestamp = value; }
        }

        internal static ILocationDao getDao(AbstractConnection cxn)
        {
            if (!cxn.IsConnected)
            {
                throw new MdoException(MdoExceptionCode.USAGE_NO_CONNECTION, "Unable to instantiate DAO: unconnected");
            }
            AbstractDaoFactory f = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(cxn.DataSource.Protocol));
            return f.getLocationDao(cxn);
        }

        public static List<SiteId> getSitesForStation(AbstractConnection cxn)
        {
            return getDao(cxn).getSitesForStation();
        }

        public static OrderedDictionary getClinicsByName(AbstractConnection cxn, string name)
        {
            return getDao(cxn).getClinicsByName(name);
        }

        public static List<Site> getAllInstitutions(AbstractConnection cxn)
        {
            return getDao(cxn).getAllInstitutions();
        }
	
    }
}
