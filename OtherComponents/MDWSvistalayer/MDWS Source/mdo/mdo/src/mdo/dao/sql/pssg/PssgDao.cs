using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;
using System.Data;
using System.Data.SqlClient;
using System.Configuration;

namespace gov.va.medora.mdo.dao.sql.pssg
{
    /// <summary>
    /// PSSG DAO - update database with CSV file found at: http://vaww.pssg.med.va.gov/PSSG/search_zipcode.html
    /// </summary>
    public class PssgDao
    {
        string _connectionString;
        SqlConnection _connection;

        public PssgDao(string connectionString)
        {
            if (String.IsNullOrEmpty(connectionString))
            {
                throw new ArgumentNullException("Must supply connection string");
            }
            _connectionString = connectionString;
        }
        ///// <summary>
        ///// [DEPRECATED] 
        ///// Use the parameterless constructor to enable reading from the configuration file
        ///// </summary>
        ///// <param name="connectionString">The SQL database connection string</param>
        //public PssgDao(string connectionString) 
        //{
        //    _connectionString = connectionString;
        //}

        internal void connect()
        {
            //string cxnString = "Provider=Microsoft.Jet.OLEDB.4.0; User Id=; Password=; Data Source=" + filepath;
            //cxn = new OleDbConnection(cxnString);
            //cxn.Open();
            _connection = new SqlConnection(_connectionString);
            _connection.Open();
        }

        internal DataTable query(SqlCommand command)
        {
            connect();
            SqlDataAdapter adapter = new SqlDataAdapter(command);
            adapter.SelectCommand.Connection = _connection;
            DataTable results = new DataTable();
            int i = adapter.Fill(results);
            _connection.Close();
            return results;
        }

        public Site[] getClosestFacilities(string fips)
        {
            if (String.IsNullOrEmpty(fips))
            {
                throw new ArgumentNullException("Must include FIPS");
            }
            string statement = "SELECT DISTINCT VISN,MCCV,MCCV_NAME FROM PSSG " +
                "WHERE FIPS = @FIPS;";
            SqlCommand cmd = new SqlCommand(statement);

            SqlParameter fipsParam = new SqlParameter("@FIPS", SqlDbType.NVarChar, 255);
            fipsParam.Value = fips;
            cmd.Parameters.Add(fipsParam);

            DataTable results = query(cmd);
            if (results == null || results.Rows == null || results.Rows.Count == 0)
            {
                return null;
            }

            Site[] sites = new Site[results.Rows.Count];
            for (int i = 0; i < results.Rows.Count; i++)
            {
                sites[i] = new Site();
                sites[i].RegionId = results.Rows[i]["VISN"] as string;
                sites[i].Id = results.Rows[i]["MCCV"] as string;
                sites[i].Name = results.Rows[i]["MCCV_NAME"] as string;
            }
            return sites;
        }

        public ClosestFacility getNearestFacility(string zipcode)
        {
            if (String.IsNullOrEmpty(zipcode))
            {
                throw new ArgumentNullException("Must include zipcode");
            }
            if (!String.IsNullOrEmpty(zipcode) && zipcode.Length == 5 && zipcode.EndsWith("00"))
            {
                zipcode = zipcode.Substring(0, 3);
                zipcode += "01";
            }
            string statement = "SELECT * FROM PSSG WHERE ZIPCODE = @ZIPCODE;";
            SqlCommand command = new SqlCommand(statement);

            SqlParameter zipcodeParam = new SqlParameter("@ZIPCODE", SqlDbType.NVarChar, 50);
            zipcodeParam.Value = zipcode;
            command.Parameters.Add(zipcodeParam);

            DataTable results = query(command);
            if (results == null || results.Rows == null || results.Rows.Count == 0)
            {
                return null;
            }
            ClosestFacility result = new ClosestFacility();
            result.RegionId = results.Rows[0][0] as string;
            result.State = results.Rows[0][1] as string;
            result.City = results.Rows[0][2] as string;
            result.Zipcode = results.Rows[0][3] as string;
            result.Latitude = results.Rows[0][4] as string;
            result.Longitude = results.Rows[0][5] as string;
            result.Fips = results.Rows[0][6] as string;
            result.County = results.Rows[0][7] as string;
            result.Urb = results.Rows[0][8] as string;
            result.Msa = results.Rows[0][9] as string;
            result.NearestFacility = 
                new Site(results.Rows[0][10] as string, results.Rows[0][11] as string);
            result.NearestFacility.SiteType = results.Rows[0][12] as string;
            result.NearestFacility.RegionId = result.RegionId;
            result.NearestFacilityDistance = results.Rows[0][13] as string;
            result.NearestFacilityMsa = result.Msa;
            result.NearestFacilityUrb = result.Urb;
            result.NearestMedicalCenter = new Site(results.Rows[0][14] as string, results.Rows[0][15] as string);
            result.NearestMedicalCenter.RegionId = results.Rows[0][16] as string;
            result.NearestMedicalCenter.SiteType = "VAMC";
            result.NearestMedicalCenterMsa = results.Rows[0][17] as string;
            result.NearestMedicalCenterUrb = results.Rows[0][18] as string;
            result.NearestMedicalCenterDistance = results.Rows[0][19] as string;
            result.NearestFacilityInRegion = new Site(results.Rows[0][20] as string, results.Rows[0][21] as string);
            result.NearestFacilityInRegion.SiteType = results.Rows[0][22] as string;
            result.NearestFacilityInRegionMsa = results.Rows[0][23] as string;
            result.NearestFacilityInRegionUrb = results.Rows[0][24] as string;
            result.NearestFacilityInRegionDistance = results.Rows[0][25] as string;
            result.NearestMedicalCenterInRegion = new Site(results.Rows[0][26] as string, results.Rows[0][27] as string);
            result.NearestMedicalCenterInRegion.SiteType = "VAMC";
            result.NearestMedicalCenterInRegionMsa = results.Rows[0][28] as string;
            result.NearestMedicalCenterInRegionUrb = results.Rows[0][29] as string;
            result.NearestMedicalCenterInRegionDistance = results.Rows[0][30] as string;
            return result;
        }


    }
}
