using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.utils;
using gov.va.medora.mdo.src.mdo;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaLocationDao : ILocationDao
    {
        AbstractConnection cxn = null;

        public VistaLocationDao(AbstractConnection cxn)
        {
            this.cxn = cxn;
        }

        public List<SiteId> getSitesForStation()
        {
            MdoQuery request = buildGetSitesForStationRequest();
            string response = (string)cxn.query(request, new MenuOption("AMOJ VL APPTFL"));
            return siteIdsToMdo(response);
        }

        internal MdoQuery buildGetSitesForStationRequest()
        {
            // This first RPC is from the AO project and the M code 
            // behind it doesn't have a bug fix that the same function
            // in the SS project does have.  The second RPC is the one
            // with the bug fix.
            //VistaQuery vq = new VistaQuery("AMOJVL CPGPI GETSITES");
            VistaQuery vq = new VistaQuery("AMOJ VL APPTFL GET SITES");
            return vq;
        }

        internal List<SiteId> siteIdsToMdo(string response)
        {
            if (String.IsNullOrEmpty(response))
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            List<SiteId> result = new List<SiteId>();
            for (int i = 0; i < lines.Length; i++)
            {
                if (String.IsNullOrEmpty(lines[i]))
                {
                    continue;
                }
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                result.Add(new SiteId(flds[1], flds[0]));
            }
            return result;
        }

        public OrderedDictionary getClinicsByName(string name)
        {
            MdoQuery request = buildGetClinicsByNameRequest(name);
            string response = (string)cxn.query(request, new MenuOption("AMOJ VL CPGPI"));
            return clinicNamesToMdo(response);
        }

        internal MdoQuery buildGetClinicsByNameRequest(string name)
        {
            VistaQuery vq = new VistaQuery("AMOJVL CPGPI CLLOOK");
            vq.addParameter(vq.LITERAL, name);
            return vq;
        }

        internal OrderedDictionary clinicNamesToMdo(string response)
        {
            if (String.IsNullOrEmpty(response))
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            OrderedDictionary result = new OrderedDictionary();
            for (int i = 0; i < lines.Length; i++)
            {
                if (String.IsNullOrEmpty(lines[i]))
                {
                    continue;
                }
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                result.Add(flds[1], flds[0]);
            }
            return result;
        }

        #region Scheduling

        #region Clinic Details

        /// <summary>
        /// Get the details of a clinic
        /// </summary>
        /// <param name="locationIen">The IEN of the clinic in the HospitalLocation file</param>
        /// <returns></returns>
        public HospitalLocation getClinicDetails(string locationIen)
        {
            MdoQuery request = buildGetClinicDetailsRequest(locationIen);
            string response = (string)cxn.query(request);
            return toLocation(response);
        }

        internal MdoQuery buildGetClinicDetailsRequest(string locationIen)
        {
            VistaQuery query = new VistaQuery("SD GET CLINIC DETAILS");
            query.addParameter(query.LITERAL, locationIen);
            return query;
        }

        // TODO - need to finish parsing
        internal HospitalLocation toLocation(string response)
        {
            HospitalLocation location = new HospitalLocation();

            if (String.IsNullOrEmpty(response))
            {
                return location;
            }

            string[] lines = StringUtils.split(response, StringUtils.CRLF);

            foreach (string line in lines)
            {
                if (String.IsNullOrEmpty(line))
                {
                    continue;
                }

                string[] pieces = StringUtils.split(line, StringUtils.EQUALS);

                if (pieces.Length < 2)
                {
                    continue;
                }

                pieces[0] = pieces[0].Replace("RESULT(\"", "");
                pieces[0] = pieces[0].Replace("\")", "");

                switch (pieces[0])
                {
                    case "ABBREVIATION" :
                        location.Abbr = StringUtils.split(pieces[1], StringUtils.CARET)[0];
                        break;
                    case "DIVISION":
                        location.Division = new KeyValuePair<string, string>(
                            StringUtils.split(pieces[1], StringUtils.CARET)[0], StringUtils.split(pieces[1], StringUtils.CARET)[1]);
                        break;
                    case "NAME":
                        location.Name = StringUtils.split(pieces[1], StringUtils.CARET)[0];
                        break;
                    case "TREATING SPECIALTY":
                        location.Specialty = new KeyValuePair<string,string>(
                            StringUtils.split(pieces[1], StringUtils.CARET)[0], StringUtils.split(pieces[1], StringUtils.CARET)[1]);
                        break;
                    case "TYPE" :
                        location.Type = StringUtils.split(pieces[1], StringUtils.CARET)[1];
                        break;
                    case "TYPE EXTENSION" :
                        location.TypeExtension = new KeyValuePair<string,string>(
                            StringUtils.split(pieces[1], StringUtils.CARET)[0], StringUtils.split(pieces[1], StringUtils.CARET)[1]);
                        break;
                    default:
                        break;
                }
            }

            return location;
        }

        #endregion

        #region Get Clinics By Name

        /// <summary>
        /// Get the entire list of clincs
        /// </summary>
        /// <returns></returns>
        public IList<HospitalLocation> getAllClinics()
        {
            MdoQuery request = buildGetAllClinicsRequest();
            string response = (string)cxn.query(request);
            return toAllClinics(response);
        }

        internal MdoQuery buildGetAllClinicsRequest()
        {
            VistaQuery query = new VistaQuery("SD GET CLINICS BY NAME");
            query.addParameter(query.LITERAL, ""); // SEARCH
            query.addParameter(query.LITERAL, ""); // START - LEAVING BLANK SEEMS TO START AT BEGINNING OF ALPHABET
            query.addParameter(query.LITERAL, ""); // NUMBER OF RESULTS - LEAVE BLANK SEEMS TO RETURN ALL
            return query;
        }

        // TODO - need to finish parsing
        internal IList<HospitalLocation> toAllClinics(string response)
        {
            IList<HospitalLocation> locations = new List<HospitalLocation>();

            if (String.IsNullOrEmpty(response))
            {
                return locations;
            }

            string[] lines = StringUtils.split(response, StringUtils.CRLF);

            if (lines == null || lines.Length == 0)
            {
                throw new MdoException(MdoExceptionCode.DATA_UNEXPECTED_FORMAT);
            }

            string[] metaLine = StringUtils.split(lines[0], StringUtils.EQUALS);
            string[] metaPieces = StringUtils.split(metaLine[1], StringUtils.CARET);
            Int32 numResult = Convert.ToInt32(metaPieces[0]);
            // metaPieces[1] = number of records requested (number argument). asterisk means all were returned
            // metaPieces[2] = ?

            for (int i = 1; i < lines.Length; i++)
            {
                string[] pieces = StringUtils.split(lines[i], StringUtils.EQUALS);

                if (pieces.Length < 2 || String.IsNullOrEmpty(pieces[1])) // at the declaration of a new result - create a new appointment type
                {
                    if (lines.Length >= i + 2) // just to be safe - check there are two more lines so we can obtain the ID and name
                    {
                        HospitalLocation current = new HospitalLocation();
                        current.Id = (StringUtils.split(lines[i + 1], StringUtils.EQUALS))[1];
                        current.Name = (StringUtils.split(lines[i + 2], StringUtils.EQUALS))[1];
                        locations.Add(current);
                    }
                }
            }

            // TBD - should we check the meta info matched the number of results found?
            return locations;
        }

        #endregion
        
        #endregion

        public List<Site> getAllInstitutions()
        {
            throw new NotImplementedException();
        }

    }
}
