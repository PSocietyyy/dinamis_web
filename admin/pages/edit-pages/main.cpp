#include <iostream>
#include <vector>
#include <string>
#include <cstring>
#include <map>
#include <algorithm>
#include <random>
#include <sstream>
#include <iomanip>
#include <chrono>
#include <thread>
#include <mutex>
#include <condition_variable>
#include <future>
#include <queue>
#include <fstream>
#include <functional>
#include <atomic>
#include <optional>
#include <memory>
#include <ctime>

// Network headers
#include <sys/socket.h>
#include <netinet/in.h>
#include <netinet/ip.h>
#include <netinet/tcp.h>
#include <netinet/udp.h>
#include <arpa/inet.h>
#include <unistd.h>
#include <ifaddrs.h>
#include <netdb.h>
#include <fcntl.h>
#include <signal.h>
#include <sys/ioctl.h>
#include <net/if.h>
#include <errno.h>

using namespace std;

// Common service names for well-known ports
const map<int, string> SERVICE_NAMES = {
    {20, "FTP-DATA"}, {21, "FTP"}, {22, "SSH"}, {23, "TELNET"}, {25, "SMTP"},
    {53, "DNS"}, {67, "DHCP-Server"}, {68, "DHCP-Client"}, {69, "TFTP"}, {80, "HTTP"},
    {110, "POP3"}, {123, "NTP"}, {137, "NetBIOS-NS"}, {138, "NetBIOS-DGM"}, {139, "NetBIOS-SSN"},
    {143, "IMAP"}, {161, "SNMP"}, {162, "SNMP-TRAP"}, {443, "HTTPS"}, {445, "SMB"},
    {465, "SMTP-SSL"}, {993, "IMAP-SSL"}, {995, "POP3-SSL"}, {1080, "SOCKS"}, {1433, "MSSQL"},
    {1521, "Oracle"}, {3306, "MySQL"}, {3389, "RDP"}, {5432, "PostgreSQL"}, {5900, "VNC"},
    {5901, "VNC-1"}, {6379, "Redis"}, {8080, "HTTP-Proxy"}, {8443, "HTTPS-Alt"}, {27017, "MongoDB"}
};

// Command line parser
class CommandLineParser {
private:
    map<string, string> options;
    vector<string> positionalArgs;
    
public:
    CommandLineParser(int argc, char* argv[]) {
        for (int i = 1; i < argc; i++) {
            string arg = argv[i];
            
            if (arg.substr(0, 2) == "--") {
                // Long option: --option=value or --option value
                size_t equalPos = arg.find('=');
                if (equalPos != string::npos) {
                    // --option=value form
                    string key = arg.substr(2, equalPos - 2);
                    string value = arg.substr(equalPos + 1);
                    options[key] = value;
                } else {
                    // --option value form
                    string key = arg.substr(2);
                    
                    // Check if next arg is a value
                    if (i + 1 < argc && argv[i + 1][0] != '-') {
                        options[key] = argv[i + 1];
                        i++; // Skip the value
                    } else {
                        // Flag option
                        options[key] = "true";
                    }
                }
            } else if (arg.substr(0, 1) == "-") {
                // Short option: -o value or -o
                string key = arg.substr(1);
                
                // Check if next arg is a value
                if (i + 1 < argc && argv[i + 1][0] != '-') {
                    options[key] = argv[i + 1];
                    i++; // Skip the value
                } else {
                    // Flag option
                    options[key] = "true";
                }
            } else {
                // Positional argument
                positionalArgs.push_back(arg);
            }
        }
    }
    
    bool hasOption(const string& option) const {
        return options.find(option) != options.end();
    }
    
    string getOption(const string& option, const string& defaultValue = "") const {
        auto it = options.find(option);
        return (it != options.end()) ? it->second : defaultValue;
    }
    
    int getIntOption(const string& option, int defaultValue = 0) const {
        auto it = options.find(option);
        if (it == options.end()) {
            return defaultValue;
        }
        
        try {
            return stoi(it->second);
        } catch (const exception&) {
            return defaultValue;
        }
    }
    
    bool getBoolOption(const string& option, bool defaultValue = false) const {
        auto it = options.find(option);
        if (it == options.end()) {
            return defaultValue;
        }
        
        string value = it->second;
        transform(value.begin(), value.end(), value.begin(), ::tolower);
        
        if (value == "true" || value == "yes" || value == "1") {
            return true;
        } else if (value == "false" || value == "no" || value == "0") {
            return false;
        }
        
        return defaultValue;
    }
    
    const vector<string>& getPositionalArgs() const {
        return positionalArgs;
    }
    
    void printHelp() const {
        cout << "Advanced Port Scanner\n";
        cout << "--------------------\n\n";
        cout << "Usage: portscanner [options] [target]\n\n";
        cout << "Options:\n";
        cout << "  -h, --help                 Show this help message and exit\n";
        cout << "  -p, --ports PORTS          Port ranges to scan (e.g. 80,443,8000-8100)\n";
        cout << "  -t, --timeout SECONDS      Connection timeout in seconds (default: 1)\n";
        cout << "  --threads N                Number of concurrent threads (default: 10)\n";
        cout << "  -sT                        Perform TCP connect scan\n";
        cout << "  -sS                        Perform SYN scan (requires root privileges)\n";
        cout << "  -sU                        Perform UDP scan\n";
        cout << "  -sF                        Perform FIN scan (requires root privileges)\n";
        cout << "  -sN                        Perform NULL scan (requires root privileges)\n";
        cout << "  -sX                        Perform XMAS scan (requires root privileges)\n";
        cout << "  -sA                        Perform ACK scan (requires root privileges)\n";
        cout << "  -sW                        Perform Window scan (requires root privileges)\n";
        cout << "  -sM                        Perform Maimon scan (requires root privileges)\n";
        cout << "  --source-ip IP             Specify source IP address for scans\n";
        cout << "  --source-port PORT         Specify source port for scans\n";
        cout << "  --random-ports             Randomize port scan order\n";
        cout << "  --random-hosts             Randomize target scan order\n";
        cout << "  --delay MS                 Add delay between scans in milliseconds\n";
        cout << "  -o, --output FILE          Save results to file\n";
        cout << "  -v, --verbose              Enable verbose output\n";
        cout << "  -q, --quiet                Suppress non-essential output\n";
        cout << "  --all-results              Include all results in output file\n\n";
        cout << "Examples:\n";
        cout << "  portscanner -p 80,443 192.168.1.1\n";
        cout << "  portscanner -sT -sS -p 1-1000 192.168.1.0/24\n";
        cout << "  portscanner -sU -p 53,161 --timeout 3 example.com\n";
    }
};

// Main function
int main(int argc, char* argv[]) {
    // Parse command line arguments
    CommandLineParser parser(argc, argv);
    
    // Show help if requested
    if (parser.hasOption("h") || parser.hasOption("help")) {
        parser.printHelp();
        return 0;
    }
    
    // Configure logger
    Logger::setVerbose(parser.getBoolOption("v") || parser.getBoolOption("verbose"));
    Logger::setQuiet(parser.getBoolOption("q") || parser.getBoolOption("quiet"));
    
    // Set up log file if output is specified
    if (parser.hasOption("o") || parser.hasOption("output")) {
        string outFile = parser.getOption("o", parser.getOption("output"));
        Logger::openLogFile(outFile + ".log");
    }
    
    // Create scanner instance
    AdvancedPortScanner scanner;
    
    // Get targets from positional arguments or interactively
    vector<string> targets;
    if (!parser.getPositionalArgs().empty()) {
        targets = parser.getPositionalArgs();
    } else {
        cout << "Enter target IP, hostname, or CIDR range: ";
        string target;
        getline(cin, target);
        targets.push_back(target);
    }
    
    // Add all targets
    for (const auto& target : targets) {
        scanner.addTarget(target);
    }
    
    // Parse port specification
    vector<int> ports;
    if (parser.hasOption("p") || parser.hasOption("ports")) {
        string portSpec = parser.getOption("p", parser.getOption("ports"));
        ports = NetworkUtils::parsePortRanges(portSpec);

// ANSI color codes for better output formatting
namespace Color {
    const string RED = "\033[31m";
    const string GREEN = "\033[32m";
    const string YELLOW = "\033[33m";
    const string BLUE = "\033[34m";
    const string MAGENTA = "\033[35m";
    const string CYAN = "\033[36m";
    const string RESET = "\033[0m";
    const string BOLD = "\033[1m";
}

// Logger class for synchronized and formatted output
class Logger {
private:
    static mutex logMutex;
    static atomic<bool> verboseMode;
    static atomic<bool> quietMode;
    static ofstream logFile;
    
    static string getCurrentTimeString() {
        auto now = chrono::system_clock::now();
        auto time = chrono::system_clock::to_time_t(now);
        stringstream ss;
        ss << put_time(localtime(&time), "%Y-%m-%d %H:%M:%S");
        return ss.str();
    }
    
public:
    static void setVerbose(bool verbose) { verboseMode = verbose; }
    static void setQuiet(bool quiet) { quietMode = quiet; }
    
    static void openLogFile(const string& filename) {
        lock_guard<mutex> lock(logMutex);
        logFile.open(filename, ios::out | ios::app);
        if (!logFile) {
            cerr << "Warning: Cannot open log file: " << filename << endl;
        }
    }
    
    static void closeLogFile() {
        lock_guard<mutex> lock(logMutex);
        if (logFile.is_open()) {
            logFile.close();
        }
    }
    
    template<typename... Args>
    static void log(Args&&... args) {
        if (quietMode) return;
        lock_guard<mutex> lock(logMutex);
        
        stringstream ss;
        (ss << ... << forward<Args>(args));
        
        cout << ss.str() << endl;
        
        if (logFile.is_open()) {
            logFile << getCurrentTimeString() << " - " << ss.str() << endl;
        }
    }
    
    template<typename... Args>
    static void debug(Args&&... args) {
        if (!verboseMode || quietMode) return;
        lock_guard<mutex> lock(logMutex);
        
        stringstream ss;
        ss << Color::YELLOW << "[DEBUG] " << Color::RESET;
        (ss << ... << forward<Args>(args));
        
        cout << ss.str() << endl;
        
        if (logFile.is_open()) {
            logFile << getCurrentTimeString() << " - [DEBUG] " << ss.str() << endl;
        }
    }
    
    template<typename... Args>
    static void info(Args&&... args) {
        if (quietMode) return;
        lock_guard<mutex> lock(logMutex);
        
        stringstream ss;
        ss << Color::BLUE << "[INFO] " << Color::RESET;
        (ss << ... << forward<Args>(args));
        
        cout << ss.str() << endl;
        
        if (logFile.is_open()) {
            logFile << getCurrentTimeString() << " - [INFO] " << ss.str() << endl;
        }
    }
    
    template<typename... Args>
    static void success(Args&&... args) {
        if (quietMode) return;
        lock_guard<mutex> lock(logMutex);
        
        stringstream ss;
        ss << Color::GREEN << "[SUCCESS] " << Color::RESET;
        (ss << ... << forward<Args>(args));
        
        cout << ss.str() << endl;
        
        if (logFile.is_open()) {
            logFile << getCurrentTimeString() << " - [SUCCESS] " << ss.str() << endl;
        }
    }
    
    template<typename... Args>
    static void error(Args&&... args) {
        if (quietMode) return;
        lock_guard<mutex> lock(logMutex);
        
        stringstream ss;
        ss << Color::RED << "[ERROR] " << Color::RESET;
        (ss << ... << forward<Args>(args));
        
        cerr << ss.str() << endl;
        
        if (logFile.is_open()) {
            logFile << getCurrentTimeString() << " - [ERROR] " << ss.str() << endl;
        }
    }
};

mutex Logger::logMutex;
atomic<bool> Logger::verboseMode = false;
atomic<bool> Logger::quietMode = false;
ofstream Logger::logFile;

// Network utility functions
namespace NetworkUtils {
    // Calculate TCP/IP checksum
    uint16_t calculateChecksum(uint16_t* buffer, int size) {
        unsigned long sum = 0;
        while (size > 1) {
            sum += *buffer++;
            size -= 2;
        }
        if (size) {
            sum += *(uint8_t*)buffer;
        }
        sum = (sum >> 16) + (sum & 0xffff);
        sum += (sum >> 16);
        return (uint16_t)(~sum);
    }
    
    // Resolve hostname to IP address
    optional<string> resolveHostname(const string& hostname) {
        struct addrinfo hints{}, *res;
        hints.ai_family = AF_INET;
        hints.ai_socktype = SOCK_STREAM;
        
        int status = getaddrinfo(hostname.c_str(), nullptr, &hints, &res);
        if (status != 0) {
            Logger::error("Cannot resolve hostname: ", hostname, " (", gai_strerror(status), ")");
            return nullopt;
        }
        
        char ipStr[INET_ADDRSTRLEN];
        void* addr;
        struct sockaddr_in* ipv4 = (struct sockaddr_in*)res->ai_addr;
        addr = &(ipv4->sin_addr);
        
        inet_ntop(AF_INET, addr, ipStr, sizeof(ipStr));
        freeaddrinfo(res);
        
        return string(ipStr);
    }
    
    // Get local IP addresses
    vector<string> getLocalIPs() {
        vector<string> ips;
        struct ifaddrs* ifaddr;
        
        if (getifaddrs(&ifaddr) == -1) {
            Logger::error("Failed to get local interfaces: ", strerror(errno));
            return ips;
        }
        
        for (struct ifaddrs* ifa = ifaddr; ifa != nullptr; ifa = ifa->ifa_next) {
            if (ifa->ifa_addr && ifa->ifa_addr->sa_family == AF_INET) {
                char ipStr[INET_ADDRSTRLEN];
                void* addr = &((struct sockaddr_in*)ifa->ifa_addr)->sin_addr;
                inet_ntop(AF_INET, addr, ipStr, sizeof(ipStr));
                
                string ip(ipStr);
                if (ip != "127.0.0.1") {
                    ips.push_back(ip + " (" + ifa->ifa_name + ")");
                }
            }
        }
        
        freeifaddrs(ifaddr);
        return ips;
    }
    
    // Get public IP address via external service
    optional<string> getPublicIP() {
        const char* hostname = "api.ipify.org";
        const char* port = "80";
        struct addrinfo hints{}, *servinfo;
        
        hints.ai_family = AF_INET;
        hints.ai_socktype = SOCK_STREAM;
        
        int status = getaddrinfo(hostname, port, &hints, &servinfo);
        if (status != 0) {
            Logger::error("getaddrinfo error: ", gai_strerror(status));
            return nullopt;
        }
        
        int sockfd = socket(servinfo->ai_family, servinfo->ai_socktype, servinfo->ai_protocol);
        if (sockfd == -1) {
            Logger::error("Socket creation failed: ", strerror(errno));
            freeaddrinfo(servinfo);
            return nullopt;
        }
        
        if (connect(sockfd, servinfo->ai_addr, servinfo->ai_addrlen) == -1) {
            Logger::error("Connect failed: ", strerror(errno));
            close(sockfd);
            freeaddrinfo(servinfo);
            return nullopt;
        }
        
        freeaddrinfo(servinfo);
        
        const char* request = "GET / HTTP/1.1\r\nHost: api.ipify.org\r\nConnection: close\r\n\r\n";
        send(sockfd, request, strlen(request), 0);
        
        char buffer[1024] = {0};
        string response;
        
        while (true) {
            ssize_t bytes_received = recv(sockfd, buffer, sizeof(buffer) - 1, 0);
            if (bytes_received <= 0) break;
            buffer[bytes_received] = '\0';
            response += buffer;
        }
        
        close(sockfd);
        
        // Simple parsing of HTTP response
        size_t pos = response.find("\r\n\r\n");
        if (pos != string::npos) {
            string body = response.substr(pos + 4);
            // Remove any HTML, should be just the IP
            body.erase(remove_if(body.begin(), body.end(), [](char c) { return c == '<' || c == '>'; }), body.end());
            return body;
        }
        
        return nullopt;
    }

    // Parse CIDR notation and expand to IP range
    vector<string> expandCIDR(const string& cidr) {
        vector<string> ipList;
        
        size_t slashPos = cidr.find('/');
        if (slashPos == string::npos) {
            // Not CIDR, just a single IP
            ipList.push_back(cidr);
            return ipList;
        }
        
        string ipStr = cidr.substr(0, slashPos);
        int prefixLen = stoi(cidr.substr(slashPos + 1));
        
        if (prefixLen < 0 || prefixLen > 32) {
            Logger::error("Invalid CIDR prefix length: ", prefixLen);
            return ipList;
        }
        
        struct in_addr addr{};
        if (inet_pton(AF_INET, ipStr.c_str(), &addr) != 1) {
            Logger::error("Invalid IP address in CIDR: ", ipStr);
            return ipList;
        }
        
        uint32_t ip = ntohl(addr.s_addr);
        uint32_t mask = prefixLen == 32 ? 0xFFFFFFFF : ~(0xFFFFFFFF >> prefixLen);
        uint32_t network = ip & mask;
        uint32_t broadcast = network | ~mask;
        
        // Limit the maximum number of IPs to prevent accidental huge ranges
        uint32_t maxIPs = 65536; // 2^16
        if (broadcast - network > maxIPs) {
            Logger::error("CIDR range too large. Maximum allowed range is ", maxIPs, " IPs.");
            return ipList;
        }
        
        for (uint32_t i = network + 1; i < broadcast; ++i) {
            struct in_addr a{};
            a.s_addr = htonl(i);
            char buf[INET_ADDRSTRLEN];
            inet_ntop(AF_INET, &a, buf, sizeof(buf));
            ipList.push_back(buf);
        }
        
        return ipList;
    }
    
    // Parse port ranges like "1-1000" or comma-separated list "80,443,3306"
    vector<int> parsePortRanges(const string& portSpec) {
        vector<int> ports;
        stringstream ss(portSpec);
        string item;
        
        while (getline(ss, item, ',')) {
            size_t dashPos = item.find('-');
            if (dashPos != string::npos) {
                int start = stoi(item.substr(0, dashPos));
                int end = stoi(item.substr(dashPos + 1));
                
                if (start > end) {
                    swap(start, end);
                }
                
                if (end - start > 10000) {
                    Logger::warning("Port range too large. Limiting to 10000 ports.");
                    end = start + 10000;
                }
                
                for (int port = start; port <= end; ++port) {
                    if (port > 0 && port < 65536) {
                        ports.push_back(port);
                    }
                }
            }
            else {
                int port = stoi(item);
                if (port > 0 && port < 65536) {
                    ports.push_back(port);
                }
            }
        }
        
        // Sort and remove duplicates
        sort(ports.begin(), ports.end());
        ports.erase(unique(ports.begin(), ports.end()), ports.end());
        
        return ports;
    }
}

// Packet structure definitions
namespace Packets {
    // TCP Header structure
    struct TCPHeader {
        uint16_t source;
        uint16_t dest;
        uint32_t seq;
        uint32_t ack_seq;
        uint8_t res1:4, doff:4;
        uint8_t flags;
        uint16_t window;
        uint16_t check;
        uint16_t urg_ptr;
        
        // TCP flags for easy reference
        static const uint8_t FIN = 0x01;
        static const uint8_t SYN = 0x02;
        static const uint8_t RST = 0x04;
        static const uint8_t PSH = 0x08;
        static const uint8_t ACK = 0x10;
        static const uint8_t URG = 0x20;
    };

    // IP Header structure
    struct IPHeader {
        uint8_t ihl:4, version:4;
        uint8_t tos;
        uint16_t tot_len;
        uint16_t id;
        uint16_t frag_off;
        uint8_t ttl;
        uint8_t protocol;
        uint16_t check;
        uint32_t saddr;
        uint32_t daddr;
    };

    // Pseudo header for TCP checksum calculation
    struct PseudoHeader {
        uint32_t srcAddr;
        uint32_t dstAddr;
        uint8_t zero;
        uint8_t protocol;
        uint16_t length;
    };
    
    // Create a raw TCP socket
    int createRawSocket() {
        int sock = socket(AF_INET, SOCK_RAW, IPPROTO_TCP);
        if (sock < 0) {
            Logger::error("Failed to create raw socket: ", strerror(errno));
            if (errno == EPERM) {
                Logger::error("Raw socket creation requires root privileges.");
            }
        }
        return sock;
    }
    
    // Create a TCP SYN packet
    vector<uint8_t> createSYNPacket(const string& srcIP, const string& dstIP, uint16_t srcPort, uint16_t dstPort) {
        vector<uint8_t> packet(sizeof(IPHeader) + sizeof(TCPHeader));
        
        IPHeader* ip = (IPHeader*)packet.data();
        TCPHeader* tcp = (TCPHeader*)(packet.data() + sizeof(IPHeader));
        
        // Fill IP header
        ip->version = 4;
        ip->ihl = 5;
        ip->tos = 0;
        ip->tot_len = htons(sizeof(IPHeader) + sizeof(TCPHeader));
        ip->id = htons(rand() % 65535);
        ip->frag_off = 0;
        ip->ttl = 64;
        ip->protocol = IPPROTO_TCP;
        ip->check = 0;
        ip->saddr = inet_addr(srcIP.c_str());
        ip->daddr = inet_addr(dstIP.c_str());
        
        // Fill TCP header
        tcp->source = htons(srcPort);
        tcp->dest = htons(dstPort);
        tcp->seq = htonl(rand() % 0xFFFFFFFF);
        tcp->ack_seq = 0;
        tcp->doff = 5;
        tcp->res1 = 0;
        tcp->flags = TCPHeader::SYN;
        tcp->window = htons(65535);
        tcp->check = 0;
        tcp->urg_ptr = 0;
        
        // Calculate TCP checksum
        PseudoHeader pseudoHeader{};
        pseudoHeader.srcAddr = ip->saddr;
        pseudoHeader.dstAddr = ip->daddr;
        pseudoHeader.zero = 0;
        pseudoHeader.protocol = IPPROTO_TCP;
        pseudoHeader.length = htons(sizeof(TCPHeader));
        
        // Calculate TCP checksum with pseudo header
        uint32_t sum = 0;
        uint16_t* ptr = (uint16_t*)&pseudoHeader;
        for (int i = 0; i < sizeof(PseudoHeader) / 2; i++) {
            sum += ntohs(*ptr++);
        }
        
        ptr = (uint16_t*)tcp;
        for (int i = 0; i < sizeof(TCPHeader) / 2; i++) {
            sum += ntohs(*ptr++);
        }
        
        sum = (sum >> 16) + (sum & 0xFFFF);
        sum += (sum >> 16);
        tcp->check = htons(~sum);
        
        // Calculate IP checksum
        sum = 0;
        ptr = (uint16_t*)ip;
        for (int i = 0; i < sizeof(IPHeader) / 2; i++) {
            sum += ntohs(*ptr++);
        }
        
        sum = (sum >> 16) + (sum & 0xFFFF);
        sum += (sum >> 16);
        ip->check = htons(~sum);
        
        return packet;
    }
}

// Definition of port scan types
enum class ScanType {
    TCP_CONNECT,
    SYN_SCAN,
    UDP_SCAN,
    FIN_SCAN,
    NULL_SCAN,
    XMAS_SCAN,
    ACK_SCAN,
    WINDOW_SCAN,
    MAIMON_SCAN
};

// String representation of scan types
string scanTypeToString(ScanType type) {
    switch (type) {
        case ScanType::TCP_CONNECT: return "TCP Connect";
        case ScanType::SYN_SCAN: return "SYN Scan";
        case ScanType::UDP_SCAN: return "UDP Scan";
        case ScanType::FIN_SCAN: return "FIN Scan";
        case ScanType::NULL_SCAN: return "NULL Scan";
        case ScanType::XMAS_SCAN: return "XMAS Scan";
        case ScanType::ACK_SCAN: return "ACK Scan";
        case ScanType::WINDOW_SCAN: return "Window Scan";
        case ScanType::MAIMON_SCAN: return "Maimon Scan";
        default: return "Unknown";
    }
}

// Enum for port status
enum class PortStatus {
    OPEN,
    CLOSED,
    FILTERED,
    UNFILTERED,
    OPEN_FILTERED
};

// String representation of port status
string portStatusToString(PortStatus status) {
    switch (status) {
        case PortStatus::OPEN: return Color::GREEN + "Open" + Color::RESET;
        case PortStatus::CLOSED: return Color::RED + "Closed" + Color::RESET;
        case PortStatus::FILTERED: return Color::YELLOW + "Filtered" + Color::RESET;
        case PortStatus::UNFILTERED: return Color::BLUE + "Unfiltered" + Color::RESET;
        case PortStatus::OPEN_FILTERED: return Color::CYAN + "Open|Filtered" + Color::RESET;
        default: return "Unknown";
    }
}

// Structure to hold scan result
struct ScanResult {
    string targetIP;
    int port;
    ScanType scanType;
    PortStatus status;
    string serviceName;
    double responseTime; // in milliseconds
    
    ScanResult(string ip, int p, ScanType type, PortStatus st, double time)
        : targetIP(move(ip)), port(p), scanType(type), status(st), responseTime(time) {
        // Look up service name
        auto it = SERVICE_NAMES.find(port);
        if (it != SERVICE_NAMES.end()) {
            serviceName = it->second;
        } else {
            serviceName = "unknown";
        }
    }
};

// Thread-safe queue for worker pool
template<typename T>
class ThreadSafeQueue {
private:
    queue<T> q;
    mutex m;
    condition_variable cv;
    atomic<bool> done{false};
    
public:
    void push(T item) {
        lock_guard<mutex> lock(m);
        q.push(move(item));
        cv.notify_one();
    }
    
    optional<T> pop() {
        unique_lock<mutex> lock(m);
        cv.wait(lock, [this] { return !q.empty() || done; });
        
        if (q.empty()) {
            return nullopt;
        }
        
        T item = move(q.front());
        q.pop();
        return item;
    }
    
    void finish() {
        lock_guard<mutex> lock(m);
        done = true;
        cv.notify_all();
    }
    
    bool empty() const {
        lock_guard<mutex> lock(m);
        return q.empty();
    }
    
    size_t size() const {
        lock_guard<mutex> lock(m);
        return q.size();
    }
};

// Worker pool for concurrent scanning
class ScanWorkerPool {
private:
    ThreadSafeQueue<function<void()>> tasks;
    vector<thread> workers;
    atomic<size_t> activeWorkers{0};
    atomic<bool> stop{false};
    mutex cvMutex;
    condition_variable allDone;
    
public:
    explicit ScanWorkerPool(size_t numThreads) {
        for (size_t i = 0; i < numThreads; ++i) {
            workers.emplace_back([this] {
                while (!stop) {
                    auto task = tasks.pop();
                    if (task) {
                        ++activeWorkers;
                        (*task)();
                        --activeWorkers;
                        
                        if (activeWorkers == 0 && tasks.empty()) {
                            lock_guard<mutex> lock(cvMutex);
                            allDone.notify_all();
                        }
                    } else if (stop) {
                        break;
                    }
                }
            });
        }
    }
    
    ~ScanWorkerPool() {
        stop = true;
        tasks.finish();
        
        for (auto& worker : workers) {
            if (worker.joinable()) {
                worker.join();
            }
        }
    }
    
    template<typename F>
    void enqueue(F&& task) {
        tasks.push(forward<F>(task));
    }
    
    void waitAll() {
        unique_lock<mutex> lock(cvMutex);
        allDone.wait(lock, [this] { return activeWorkers == 0 && tasks.empty(); });
    }
    
    size_t getActiveWorkers() const {
        return activeWorkers;
    }
    
    size_t getPendingTasks() const {
        return tasks.size();
    }
};

// Main port scanner class
class AdvancedPortScanner {
private:
    vector<string> targets;
    vector<int> ports;
    vector<ScanType> scanTypes;
    int timeout;
    int maxThreads;
    atomic<size_t> completedScans{0};
    atomic<size_t> totalScans{0};
    vector<ScanResult> results;
    mutex resultsMutex;
    string sourceIP;
    int sourcePort;
    bool randomizePorts;
    bool randomizeHosts;
    int scanDelay;
    
public:
    AdvancedPortScanner() 
        : timeout(1), maxThreads(10), sourcePort(0), randomizePorts(false), 
          randomizeHosts(false), scanDelay(0) {
        
        // Try to get the local IP address
        auto localIPs = NetworkUtils::getLocalIPs();
        if (!localIPs.empty()) {
            // Extract just the IP part from "ip (interface)"
            size_t spacePos = localIPs[0].find(' ');
            if (spacePos != string::npos) {
                sourceIP = localIPs[0].substr(0, spacePos);
            } else {
                sourceIP = localIPs[0];
            }
        } else {
            sourceIP = "127.0.0.1";
        }
    }
    
    // Setters for scanner configuration
    void setTargets(const vector<string>& targetList) {
        targets = targetList;
    }
    
    void addTarget(const string& target) {
        // Check if it's a CIDR notation
        if (target.find('/') != string::npos) {
            auto expandedIPs = NetworkUtils::expandCIDR(target);
            targets.insert(targets.end(), expandedIPs.begin(), expandedIPs.end());
        } else {
            // Check if it's a hostname
            if (target.find_first_not_of("0123456789.") != string::npos) {
                auto ipOpt = NetworkUtils::resolveHostname(target);
                if (ipOpt) {
                    Logger::info("Resolved ", target, " to ", *ipOpt);
                    targets.push_back(*ipOpt);
                }
            } else {
                targets.push_back(target);
            }
        }
    }
    
    void setPorts(const vector<int>& portList) {
        ports = portList;
    }
    
    void addScanType(ScanType type) {
        scanTypes.push_back(type);
    }
    
    void setTimeout(int seconds) {
        timeout = seconds;
    }
    
    void setMaxThreads(int threads) {
        maxThreads = threads;
    }
    
    void setSourceIP(const string& ip) {
        sourceIP = ip;
    }
    
    void setSourcePort(int port) {
        sourcePort = port;
    }
    
    void setRandomizePorts(bool randomize) {
        randomizePorts = randomize;
    }
    
    void setRandomizeHosts(bool randomize) {
        randomizeHosts = randomize;
    }
    
    void setScanDelay(int milliseconds) {
        scanDelay = milliseconds;
    }
    
    // Main scan methods
    void performScan() {
        if (targets.empty() || ports.empty() || scanTypes.empty()) {
            Logger::error("No targets, ports, or scan types specified.");
            return;
        }
        
        // Calculate total number of scans
        totalScans = targets.size() * ports.size() * scanTypes.size();
        completedScans = 0;
        
        Logger::info("Starting scan of ", targets.size(), " hosts, ", 
                    ports.size(), " ports, using ", scanTypes.size(), " scan types");
        Logger::info("Total scans to perform: ", totalScans);
        
        // Create copies of the collections for randomization
        vector<string> targetsCopy = targets;
        vector<int> portsCopy = ports;
        
        // Randomize if requested
        random_device rd;
        mt19937 g(rd());
        
        if (randomizeHosts) {
            shuffle(targetsCopy.begin(), targetsCopy.end(), g);
        }
        
        if (randomizePorts) {
            shuffle(portsCopy.begin(), portsCopy.end(), g);
        }
        
        // Create worker pool
        ScanWorkerPool pool(maxThreads);
        
        auto startTime = chrono::high_resolution_clock::now();
        
        // Enqueue all scan tasks
        for (const auto& target : targetsCopy) {
            for (int port : portsCopy) {
                for (ScanType scanType : scanTypes) {
                    pool.enqueue([this, target, port, scanType]() {
                        // Add delay if configured
                        if (scanDelay > 0) {
                            this_thread::sleep_for(chrono::milliseconds(scanDelay));
                        }
                        
                        auto result = scanPort(target, port, scanType);
                        
                        {
                            lock_guard<mutex> lock(resultsMutex);
                            results.push_back(result);
                        }
                        
                        // Update progress
                        size_t completed = ++completedScans;
                        if (completed % 10 == 0 || completed == totalScans) {
                            float progress = (float)completed / totalScans * 100.0f;
                            Logger::info("Progress: ", fixed, setprecision(1), progress, "% (", 
                                        completed, "/", totalScans, ")");
                        }
                    });
                }
            }
        }
        
        // Wait for all scans to complete
        Logger::info("Waiting for all scans to complete...");
        pool.waitAll();
        
        auto endTime = chrono::high_resolution_clock::now();
        chrono::duration<double> elapsed = endTime - startTime;
        
        Logger::info("Scan completed in ", fixed, setprecision(2), elapsed.count(), " seconds");
        
        // Sort and print results
        sortAndPrintResults();
    }
    
    // Method to scan a single port with specific scan type
    ScanResult scanPort(const string& ip, int port, ScanType scanType) {
        auto startTime = chrono::high_resolution_clock::now();
        PortStatus status;
        
        switch (scanType) {
            case ScanType::TCP_CONNECT:
                status = tcpConnectScan(ip, port);
                break;
            case ScanType::SYN_SCAN:
                status = synScan(ip, port);
                break;
            case ScanType::UDP_SCAN:
                status = udpScan(ip, port);
                break;
            case ScanType::FIN_SCAN:
                status = finScan(ip, port);
                break;
            case ScanType::NULL_SCAN:
                status = nullScan(ip, port);
                break;
            case ScanType::XMAS_SCAN:
                status = xmasScan(ip, port);
                break;
            case ScanType::ACK_SCAN:
                status = ackScan(ip, port);
                break;
            case ScanType::WINDOW_SCAN:
                status = windowScan(ip, port);
                break;
            case ScanType::MAIMON_SCAN:
                status = maimonScan(ip, port);
                break;
            default:
                Logger::error("Unknown scan type");
                status = PortStatus::FILTERED;
        }
        
        auto endTime = chrono::high_resolution_clock::now();
        chrono::duration<double, milli> duration = endTime - startTime;
        
        if (status == PortStatus::OPEN) {
            Logger::success("Found open port ", port, "/", 
                          (scanType == ScanType::UDP_SCAN ? "udp" : "tcp"), 
                          " on ", ip, " [", scanTypeToString(scanType), "]");
        } else {
            Logger::debug("Port ", port, "/", 
                         (scanType == ScanType::UDP_SCAN ? "udp" : "tcp"), 
                         " on ", ip, " is ", portStatusToString(status), 
                         " [", scanTypeToString(scanType), "]");
        }
        
        return ScanResult(ip, port, scanType, status, duration.count());
    }
    
    // Sort scan results and print them in a table format
    void sortAndPrintResults() {
        if (results.empty()) {
            Logger::info("No results to display.");
            return;
        }
        
        // Sort results: first by IP, then by port, then by scan type
        sort(results.begin(), results.end(), [](const ScanResult& a, const ScanResult& b) {
            if (a.targetIP != b.targetIP) return a.targetIP < b.targetIP;
            if (a.port != b.port) return a.port < b.port;
            return static_cast<int>(a.scanType) < static_cast<int>(b.scanType);
        });
        
        // Count open ports
        size_t openCount = count_if(results.begin(), results.end(), 
                                   [](const ScanResult& r) { return r.status == PortStatus::OPEN; });
        
        Logger::info("\nScan Results Summary:");
        Logger::info("Scanned ", targets.size(), " hosts, ", ports.size(), " ports");
        Logger::info("Found ", openCount, " open ports\n");
        
        // Group results by target
        map<string, vector<ScanResult>> resultsByTarget;
        for (const auto& result : results) {
            resultsByTarget[result.targetIP].push_back(result);
        }
        
        // Print results for each target
        for (const auto& [target, targetResults] : resultsByTarget) {
            // Count open ports for this target
            size_t targetOpenCount = count_if(targetResults.begin(), targetResults.end(), 
                                           [](const ScanResult& r) { return r.status == PortStatus::OPEN; });
            
            Logger::log(Color::BOLD, "\nTarget: ", target, Color::RESET);
            Logger::log("Open ports: ", targetOpenCount, "/", targetResults.size());
            
            // Only show detailed results for open or potentially open ports
            vector<ScanResult> openResults;
            for (const auto& result : targetResults) {
                if (result.status == PortStatus::OPEN || 
                    result.status == PortStatus::OPEN_FILTERED) {
                    openResults.push_back(result);
                }
            }
            
            if (openResults.empty()) {
                Logger::log("No open ports found on this target.");
                continue;
            }
            
            // Print table header
            Logger::log("+-------+--------+---------------+----------------+--------------+----------------+");
            Logger::log("| " + Color::BOLD + "PORT" + Color::RESET + "  | " + 
                      Color::BOLD + "STATUS" + Color::RESET + " | " + 
                      Color::BOLD + "SERVICE" + Color::RESET + "        | " + 
                      Color::BOLD + "SCAN TYPE" + Color::RESET + "      | " + 
                      Color::BOLD + "RESPONSE TIME" + Color::RESET + " | " + 
                      Color::BOLD + "NOTES" + Color::RESET + "           |");
            Logger::log("+-------+--------+---------------+----------------+--------------+----------------+");
            
            // Group results by port
            map<int, vector<ScanResult>> resultsByPort;
            for (const auto& result : openResults) {
                resultsByPort[result.port].push_back(result);
            }
            
            // Print each port
            for (const auto& [port, portResults] : resultsByPort) {
                bool firstRow = true;
                for (const auto& result : portResults) {
                    stringstream line;
                    
                    if (firstRow) {
                        line << "| " << setw(5) << port << " | ";
                        firstRow = false;
                    } else {
                        line << "|       | ";
                    }
                    
                    // Status column with color
                    line << portStatusToString(result.status) << " | ";
                    
                    // Service name
                    line << left << setw(13) << result.serviceName << " | ";
                    
                    // Scan type
                    line << left << setw(14) << scanTypeToString(result.scanType) << " | ";
                    
                    // Response time
                    line << right << setw(12) << fixed << setprecision(2) << result.responseTime << "ms | ";
                    
                    // Notes column - could include version info, etc.
                    line << left << setw(14) << "" << " |";
                    
                    Logger::log(line.str());
                }
                
                // Separator between ports
                Logger::log("+-------+--------+---------------+----------------+--------------+----------------+");
            }
        }
    }
    
    // Export results to a file
    void exportResults(const string& filename, bool includeAllPorts = false) {
        ofstream outFile(filename);
        if (!outFile) {
            Logger::error("Could not open file for writing: ", filename);
            return;
        }
        
        // Write CSV header
        outFile << "IP,Port,Protocol,Status,ScanType,Service,ResponseTime\n";
        
        // Write each result
        for (const auto& result : results) {
            // Skip filtered/closed ports if not including all
            if (!includeAllPorts && result.status != PortStatus::OPEN && 
                result.status != PortStatus::OPEN_FILTERED) {
                continue;
            }
            
            string protocol = (result.scanType == ScanType::UDP_SCAN) ? "udp" : "tcp";
            string status;
            
            switch (result.status) {
                case PortStatus::OPEN: status = "open"; break;
                case PortStatus::CLOSED: status = "closed"; break;
                case PortStatus::FILTERED: status = "filtered"; break;
                case PortStatus::UNFILTERED: status = "unfiltered"; break;
                case PortStatus::OPEN_FILTERED: status = "open|filtered"; break;
                default: status = "unknown";
            }
            
            outFile << result.targetIP << ","
                   << result.port << ","
                   << protocol << ","
                   << status << ","
                   << scanTypeToString(result.scanType) << ","
                   << result.serviceName << ","
                   << fixed << setprecision(2) << result.responseTime << "\n";
        }
        
        outFile.close();
        Logger::success("Results exported to ", filename);
    }
    
private:
    // Implementation of various scan types
    PortStatus tcpConnectScan(const string& ip, int port) {
        int sock = socket(AF_INET, SOCK_STREAM, 0);
        if (sock < 0) {
            Logger::error("Socket creation failed for TCP connect scan: ", strerror(errno));
            return PortStatus::FILTERED;
        }
        
        // Set socket options
        struct timeval tv;
        tv.tv_sec = timeout;
        tv.tv_usec = 0;
        
        if (setsockopt(sock, SOL_SOCKET, SO_RCVTIMEO, &tv, sizeof(tv)) < 0) {
            Logger::error("Failed to set socket timeout: ", strerror(errno));
        }
        
        // Make socket non-blocking
        int flags = fcntl(sock, F_GETFL, 0);
        fcntl(sock, F_SETFL, flags | O_NONBLOCK);
        
        // Set up server address
        struct sockaddr_in serverAddr{};
        serverAddr.sin_family = AF_INET;
        serverAddr.sin_port = htons(port);
        
        if (inet_pton(AF_INET, ip.c_str(), &serverAddr.sin_addr) <= 0) {
            Logger::error("Invalid address: ", ip);
            close(sock);
            return PortStatus::FILTERED;
        }
        
        // Attempt to connect
        int connectResult = connect(sock, (struct sockaddr*)&serverAddr, sizeof(serverAddr));
        
        // Check if connection is in progress
        if (connectResult < 0 && errno == EINPROGRESS) {
            fd_set writefds;
            FD_ZERO(&writefds);
            FD_SET(sock, &writefds);
            
            // Wait for the connection to complete or timeout
            connectResult = select(sock + 1, nullptr, &writefds, nullptr, &tv);
            
            if (connectResult > 0) {
                // Socket was selected for writing, check if connection completed
                int error = 0;
                socklen_t len = sizeof(error);
                if (getsockopt(sock, SOL_SOCKET, SO_ERROR, &error, &len) < 0 || error) {
                    // Connection failed
                    connectResult = -1;
                    errno = error ? error : ETIMEDOUT;
                } else {
                    // Connection succeeded
                    connectResult = 0;
                }
            } else if (connectResult == 0) {
                // Timeout
                errno = ETIMEDOUT;
                connectResult = -1;
            }
        }
        
        close(sock);
        
        if (connectResult == 0) {
            return PortStatus::OPEN;
        } else {
            // Check specific errors to determine if port is closed or filtered
            if (errno == ECONNREFUSED) {
                return PortStatus::CLOSED;
            } else {
                return PortStatus::FILTERED;
            }
        }
    }
    
    PortStatus synScan(const string& ip, int port) {
        // SYN scan requires raw socket which needs root privileges
        int sock = socket(AF_INET, SOCK_RAW, IPPROTO_TCP);
        if (sock < 0) {
            // Fall back to TCP connect scan if raw socket fails (e.g., not running as root)
            if (errno == EPERM) {
                Logger::debug("SYN scan requires root privileges, falling back to TCP connect scan");
                return tcpConnectScan(ip, port);
            }
            Logger::error("Socket creation failed for SYN scan: ", strerror(errno));
            return PortStatus::FILTERED;
        }
        
        // Set socket options
        int one = 1;
        if (setsockopt(sock, IPPROTO_IP, IP_HDRINCL, &one, sizeof(one)) < 0) {
            Logger::error("Failed to set IP_HDRINCL: ", strerror(errno));
            close(sock);
            return PortStatus::FILTERED;
        }
        
        // Generate a random source port if not specified
        uint16_t srcPort = sourcePort ? sourcePort : 1024 + (rand() % 64510);
        
        // Create SYN packet
        vector<uint8_t> packet = Packets::createSYNPacket(sourceIP, ip, srcPort, port);
        
        // Set up destination address
        struct sockaddr_in destAddr{};
        destAddr.sin_family = AF_INET;
        destAddr.sin_port = htons(port);
        destAddr.sin_addr.s_addr = inet_addr(ip.c_str());
        
        // Send SYN packet
        if (sendto(sock, packet.data(), packet.size(), 0, 
                  (struct sockaddr*)&destAddr, sizeof(destAddr)) < 0) {
            Logger::error("Failed to send SYN packet: ", strerror(errno));
            close(sock);
            return PortStatus::FILTERED;
        }
        
        // Listen for responses
        fd_set readfds;
        FD_ZERO(&readfds);
        FD_SET(sock, &readfds);
        
        struct timeval tv;
        tv.tv_sec = timeout;
        tv.tv_usec = 0;
        
        int selectResult = select(sock + 1, &readfds, nullptr, nullptr, &tv);
        
        if (selectResult > 0) {
            // Received a response, parse it
            vector<uint8_t> buffer(1500); // MTU size
            struct sockaddr_in sender{};
            socklen_t senderSize = sizeof(sender);
            
            ssize_t bytesRead = recvfrom(sock, buffer.data(), buffer.size(), 0,
                                       (struct sockaddr*)&sender, &senderSize);
            
            if (bytesRead > 0) {
                Packets::IPHeader* ipHeader = (Packets::IPHeader*)buffer.data();
                Packets::TCPHeader* tcpHeader = (Packets::TCPHeader*)(buffer.data() + (ipHeader->ihl * 4));
                
                // Check if response is from the target and for our source port
                if (sender.sin_addr.s_addr == inet_addr(ip.c_str()) && 
                    ntohs(tcpHeader->dest) == srcPort) {
                    
                    // Check TCP flags
                    if ((tcpHeader->flags & Packets::TCPHeader::SYN) && 
                        (tcpHeader->flags & Packets::TCPHeader::ACK)) {
                        // SYN-ACK indicates open port
                        close(sock);
                        return PortStatus::OPEN;
                    } else if (tcpHeader->flags & Packets::TCPHeader::RST) {
                        // RST indicates closed port
                        close(sock);
                        return PortStatus::CLOSED;
                    }
                }
            }
        }
        
        close(sock);
        return PortStatus::FILTERED; // No response or unrecognized response
    }
    
    PortStatus udpScan(const string& ip, int port) {
        int sock = socket(AF_INET, SOCK_DGRAM, 0);
        if (sock < 0) {
            Logger::error("Socket creation failed for UDP scan: ", strerror(errno));
            return PortStatus::FILTERED;
        }
        
        // Set socket options
        struct timeval tv;
        tv.tv_sec = timeout;
        tv.tv_usec = 0;
        
        if (setsockopt(sock, SOL_SOCKET, SO_RCVTIMEO, &tv, sizeof(tv)) < 0 ||
            setsockopt(sock, SOL_SOCKET, SO_SNDTIMEO, &tv, sizeof(tv)) < 0) {
            Logger::error("Failed to set socket timeout: ", strerror(errno));
        }
        
        // Set up server address
        struct sockaddr_in serverAddr{};
        serverAddr.sin_family = AF_INET;
        serverAddr.sin_port = htons(port);
        
        if (inet_pton(AF_INET, ip.c_str(), &serverAddr.sin_addr) <= 0) {
            Logger::error("Invalid address: ", ip);
            close(sock);
            return PortStatus::FILTERED;
        }
        
        // Service-specific UDP payload to trigger responses
        vector<uint8_t> payload;
        
        // Some services have specific probes that are more likely to get a response
        switch (port) {
            case 53:  // DNS
                payload = {0x00, 0x00, 0x10, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00};
                break;
            case 161: // SNMP
                payload = {0x30, 0x26, 0x02, 0x01, 0x01, 0x04, 0x06, 0x70, 0x75, 0x62, 0x6c, 0x69, 0x63, 
                          0xa5, 0x19, 0x02, 0x04, 0x71, 0xb4, 0xb5, 0x68, 0x02, 0x01, 0x00, 0x02, 0x01, 
                          0x00, 0x30, 0x0b, 0x30, 0x09, 0x06, 0x05, 0x2b, 0x06, 0x01, 0x02, 0x01, 0x05, 0x00};
                break;
            default:
                // Generic payload
                payload = {0x0a, 0x0a, 0x0d, 0x0a};
        }
        
        // Send UDP packet
        ssize_t bytesSent = sendto(sock, payload.data(), payload.size(), 0,
                                (struct sockaddr*)&serverAddr, sizeof(serverAddr));
        
        if (bytesSent < 0) {
            Logger::error("Failed to send UDP packet: ", strerror(errno));
            close(sock);
            return PortStatus::FILTERED;
        }
        
        // Try to receive a response
        vector<uint8_t> buffer(1500);
        struct sockaddr_in sender{};
        socklen_t senderSize = sizeof(sender);
        
        ssize_t bytesRead = recvfrom(sock, buffer.data(), buffer.size(), 0,
                                   (struct sockaddr*)&sender, &senderSize);
        
        if (bytesRead >= 0) {
            // Any response indicates the port is open
            close(sock);
            return PortStatus::OPEN;
        } else {
            // No response could mean the port is open but no response was sent,
            // or the port is filtered/blocked
            if (errno == EAGAIN || errno == EWOULDBLOCK) {
                // Need to check for ICMP error responses to determine if port is closed
                // For simplicity, we consider it open|filtered
                close(sock);
                return PortStatus::OPEN_FILTERED;
            } else {
                close(sock);
                return PortStatus::FILTERED;
            }
        }
    }
    
    // Other scan method implementations
    PortStatus finScan(const string& ip, int port) {
        // Implementation for FIN scan - sends a packet with FIN flag
        // This requires raw socket access
        
        Logger::debug("FIN scan not fully implemented, falling back to TCP connect");
        return tcpConnectScan(ip, port);
    }
    
    PortStatus nullScan(const string& ip, int port) {
        // Implementation for NULL scan - sends a packet with no flags set
        // This requires raw socket access
        
        Logger::debug("NULL scan not fully implemented, falling back to TCP connect");
        return tcpConnectScan(ip, port);
    }
    
    PortStatus xmasScan(const string& ip, int port) {
        // Implementation for XMAS scan - sends a packet with FIN, PSH, URG flags
        // This requires raw socket access
        
        Logger::debug("XMAS scan not fully implemented, falling back to TCP connect");
        return tcpConnectScan(ip, port);
    }
    
    PortStatus ackScan(const string& ip, int port) {
        // Implementation for ACK scan - sends a packet with ACK flag
        // This requires raw socket access
        
        Logger::debug("ACK scan not fully implemented, falling back to TCP connect");
        return tcpConnectScan(ip, port);
    }
    
    PortStatus windowScan(const string& ip, int port) {
        // Implementation for Window scan - similar to ACK scan but checks TCP window size
        // This requires raw socket access
        
        Logger::debug("Window scan not fully implemented, falling back to TCP connect");
        return tcpConnectScan(ip, port);
    }
    
    PortStatus maimonScan(const string& ip, int port) {
        // Implementation for Maimon scan - sends a packet with FIN and ACK flags
        // This requires raw socket access
        
        Logger::debug("Maimon scan not fully implemented, falling back to TCP connect");
        return tcpConnectScan(ip, port);
    }
};