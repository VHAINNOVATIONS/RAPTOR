using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Data;
using gov.va.medora.mdo.dao;
using System.Data.SqlClient;
using gov.va.medora.utils;
using gov.va.medora.mdo.dao.sql.cdw;

namespace gov.va.medora.mdo.dao
{
    class CdwLocationDao : ILocationDao
    {
        CdwConnection _cxn;

        public CdwLocationDao(AbstractConnection cxn)
        {
            _cxn = cxn as CdwConnection;
        }

        public List<SiteId> getSitesForStation()
        {
            throw new NotImplementedException();
        }

        public System.Collections.Specialized.OrderedDictionary getClinicsByName(string name)
        {
            throw new NotImplementedException();
        }

        public List<Site> getAllInstitutions()
        {
            SqlDataAdapter adapter = buildAllInstitutionsRequest();
            IDataReader reader = (IDataReader)_cxn.query(adapter);
            return toInstitutionList(reader);
        }

        internal SqlDataAdapter buildAllInstitutionsRequest()
        {
            String queryString = "SELECT * FROM App.AllInstitutions";

            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(queryString);

            return adapter;
        }

        internal List<Site> toInstitutionList(IDataReader reader)
        {
            List<Site> institutionResults = new List<Site>();

            try
            {
                while (reader.Read())
                {
                    Site site = createSiteFromAllInstitutionsReader(reader);
                    institutionResults.Add(site);
                }
            }
            catch (Exception)
            { }
            finally
            {
                reader.Close();
            }

            return institutionResults;
        }

        public Site createSiteFromAllInstitutionsReader(IDataReader reader){
            string id = DbReaderUtil.getInt32Value(reader, reader.GetOrdinal("InstitutionSID"));
            string code = DbReaderUtil.getValue(reader, reader.GetOrdinal("InstitutionCode"));
            string name = DbReaderUtil.getValue(reader, reader.GetOrdinal("InstitutionName"));
            string type = DbReaderUtil.getValue(reader, reader.GetOrdinal("FacilityType"));
            string sta3n = DbReaderUtil.getInt16Value(reader, reader.GetOrdinal("Sta3n"));
            string streetAddress1 = DbReaderUtil.getValue(reader, reader.GetOrdinal("StreetAddress1"));
            string streetAddress2 = DbReaderUtil.getValue(reader, reader.GetOrdinal("StreetAddress2"));
            string city = DbReaderUtil.getValue(reader, reader.GetOrdinal("City"));
            string state = DbReaderUtil.getValue(reader, reader.GetOrdinal("StateAbbrev"));
            string zipCode = DbReaderUtil.getValue(reader, reader.GetOrdinal("Zip"));

            string address = formatAddress(streetAddress1, streetAddress2, city, state, zipCode);

            Site site = new Site()
            {
                Id = id,
                Name = name,
                SystemName = code,
                ParentSiteId = sta3n,
                SiteType = type,
                Address = address,
                City = city,
                State = state,
            };
            return site;
        }

        internal string formatAddress(string streetAddress1, string streetAddress2, string city, string state, string zip) {
            StringBuilder builder = new StringBuilder();
            builder.Append(streetAddress1);
            builder.Append(" \n");
            builder.Append(streetAddress2);
            builder.Append(" \n");
            builder.Append(city);
            builder.Append(", ");
            builder.Append(state);
            builder.Append(" ");
            builder.Append(zip);

            return builder.ToString();
        }


        
    }
}
